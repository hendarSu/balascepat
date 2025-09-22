<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $videos = $user->videos()->get();

        $originalBytes = (int) $videos->sum(fn ($v) => (int) ($v->file_size ?? 0));

        // Aggregate HLS bytes by matching per-video prefix: video_{id}_
        $hlsBytes = 0;
        try {
            $disk = Storage::disk('minio');
            // List all HLS files once to keep calls minimal
            $hlsFiles = collect($disk->allFiles('hls'));
            foreach ($videos as $video) {
                $prefix = 'video_'.$video->id.'_';
                $matching = $hlsFiles->filter(function ($path) use ($prefix) {
                    return Str::startsWith(basename($path), $prefix);
                });
                foreach ($matching as $path) {
                    try {
                        $hlsBytes += (int) $disk->size($path);
                    } catch (\Throwable $e) {
                        // ignore size errors for individual files
                    }
                }
            }
        } catch (\Throwable $e) {
            // If storage not available, leave as 0 and show a hint on view
        }

        $totalBytes = $originalBytes + $hlsBytes;

        // Traffic: last 14 days by day
        $trafficDays = [];
        try {
            $ids = $videos->pluck('id');
            $events = \App\Models\VideoEvent::query()
                ->whereIn('video_id', $ids)
                ->whereBetween('created_at', [now()->subDays(13)->startOfDay(), now()])
                ->where('event', 'play')
                ->get(['id','created_at']);
            $byDay = [];
            for ($i = 13; $i >= 0; $i--) {
                $day = now()->subDays($i)->format('Y-m-d');
                $byDay[$day] = 0;
            }
            foreach ($events as $e) {
                $d = $e->created_at->format('Y-m-d');
                if (isset($byDay[$d])) $byDay[$d]++;
            }
            foreach ($byDay as $d => $c) { $trafficDays[] = ['date' => $d, 'count' => $c]; }
        } catch (\Throwable $e) {}

        // Popular videos: last 30 days by play count (fallback to view_count)
        $popular = [];
        try {
            $ids = $videos->pluck('id');
            $rows = \DB::table('video_events')
                ->select('video_id', \DB::raw('count(*) as plays'))
                ->whereIn('video_id', $ids)
                ->where('event', 'play')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('video_id')
                ->orderByDesc('plays')
                ->limit(5)
                ->get();
            $byId = $rows->keyBy('video_id');
            $videos->each(function ($v) use (&$popular, $byId) {
                $plays = (int) ($byId[$v->id]->plays ?? 0);
                $popular[] = [
                    'id' => $v->id,
                    'title' => $v->title,
                    'plays' => $plays,
                    'created_at' => $v->created_at,
                    'hls_path' => $v->hls_path,
                    'status' => $v->status,
                ];
            });
            usort($popular, fn($a,$b) => $b['plays'] <=> $a['plays']);
            $popular = array_slice($popular, 0, 5);
        } catch (\Throwable $e) {
            // fallback by view_count
            $popular = $videos->sortByDesc('view_count')->take(5)->map(fn($v)=>[
                'id'=>$v->id,'title'=>$v->title,'plays'=>$v->view_count,'created_at'=>$v->created_at,'hls_path'=>$v->hls_path,'status'=>$v->status
            ])->values()->all();
        }

        return view('dashboard', [
            'storage' => [
                'original' => $originalBytes,
                'hls' => $hlsBytes,
                'total' => $totalBytes,
            ],
            'trafficDays' => $trafficDays,
            'popular' => $popular,
        ]);
    }
}
