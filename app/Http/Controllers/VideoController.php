<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use ProtoneMedia\LaravelFFMpeg\Exporters\HLSExporter;
use App\Models\Video;
use FFMpeg\Format\Video\X264;

class VideoController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth')->except([
        //     'servePlaylist',
        //     'serveKey',
        //     'showPlayer'
        // ]);
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $perPage = (int) $request->integer('per_page', 10);
        $perPage = max(5, min(100, $perPage));

        $query = $user->videos()->newQuery();

        // Search
        if ($search = trim((string) $request->get('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('original_filename', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status = $request->get('status')) {
            if (in_array($status, ['uploaded','processing','completed','failed'])) {
                $query->where('status', $status);
            }
        }

        // Visibility filter
        if (!is_null($request->get('visibility'))) {
            $visibility = $request->get('visibility');
            if ($visibility === 'public') {
                $query->where('is_public', true);
            } elseif ($visibility === 'private') {
                $query->where('is_public', false);
            }
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'most_viewed':
                $query->orderByDesc('view_count')->orderByDesc('id');
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
            default:
                $query->orderByDesc('created_at');
        }

        $videos = $query->paginate($perPage)->withQueryString();

        return view('video.index', [
            'videos' => $videos,
            'filters' => [
                'q' => $search ?? '',
                'status' => $status ?? '',
                'visibility' => $request->get('visibility', ''),
                'sort' => $sort,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create()
    {
        return view('video.create');
    }

    /**
     * Upload video ke MinIO dan simpan ke database
     */
    public function uploadVideo(Request $request)
    {
        try {
            $request->validate([
                'video' => 'required|file|mimes:mp4,avi,mov,wmv|max:102400',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'is_public' => 'boolean'
            ]);
            $file = $request->file('video');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = Storage::disk('minio')->putFileAs('videos', $file, $fileName);

            $video = Video::create([
                'user_id' => auth()->id(),
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'status' => 'uploaded',
                'is_public' => $request->boolean('is_public', false),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Video uploaded successfully',
                'data' => [
                    'video_id' => $video->id,
                    'file_path' => $path,
                    'file_name' => $fileName,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading video: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process video menjadi HLS
     */
    public function convertToEncryptedHLS(Request $request)
    {
        try {
            $request->validate([
                'video_id' => 'required|exists:videos,id',
                'encryption_type' => 'required|in:single,rotating',
            ]);

            $video = Video::findOrFail($request->video_id);

            if ($video->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to video'
                ], 403);
            }

            if (empty($video->file_path) || $video->file_path === '0' || !Storage::disk('minio')->exists($video->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or missing video file path.'
                ], 400);
            }

            $video->update(['status' => 'processing']);
            $outputName = 'video_' . $video->id . '_' . time();
            $encryptionKeys = [];

            $lowBitrate = (new X264('aac', 'libx264'))
                ->setKiloBitrate(500)
                ->setAudioKiloBitrate(64);

            $midBitrate = (new X264('aac', 'libx264'))
                ->setKiloBitrate(1000)
                ->setAudioKiloBitrate(128);

            $highBitrate = (new X264('aac', 'libx264'))
                ->setKiloBitrate(2000)
                ->setAudioKiloBitrate(192);

            if ($request->encryption_type === 'single') {
                $encryptionKey = HLSExporter::generateEncryptionKey();

                $keyFileName = $outputName . '.key';
                Storage::disk('minio')->put(
                    "keys/{$keyFileName}",
                    $encryptionKey
                );
                $encryptionKeys[] = $keyFileName;

                FFMpeg::fromDisk('minio')
                    ->open($video->file_path)
                    ->exportForHLS()
                    ->withEncryptionKey($encryptionKey, $keyFileName)
                    ->addFormat($lowBitrate)
                    ->addFormat($midBitrate)
                    ->addFormat($highBitrate)
                    ->toDisk('minio')
                    ->save("hls/{$outputName}.m3u8");
            } else {
                FFMpeg::fromDisk('minio')
                    ->open($video->file_path)
                    ->exportForHLS()
                    ->withRotatingEncryptionKey(function ($filename, $contents) use (&$encryptionKeys) {
                        Storage::disk('minio')->put(
                            "keys/{$filename}",
                            $contents
                        );
                        $encryptionKeys[] = $filename;
                    })
                    ->addFormat($lowBitrate)
                    ->addFormat($midBitrate)
                    ->addFormat($highBitrate)
                    ->toDisk('minio')
                    ->save("hls/{$outputName}.m3u8");
            }

            $video->update([
                'status' => 'completed',
                'hls_path' => "hls/{$outputName}.m3u8",
                'encryption_type' => $request->encryption_type,
                'encryption_keys' => $encryptionKeys
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Video berhasil dikonversi ke HLS dengan enkripsi',
                'data' => [
                    'video_id' => $video->id,
                    'playlist_url' => $video->getPlaylistUrl(),
                    'encryption_type' => $request->encryption_type,
                    'output_name' => $outputName
                ]
            ]);
        } catch (\Exception $e) {
            if (isset($video)) {
                $video->update(['status' => 'failed']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Melayani playlist HLS dengan dynamic URL resolution
     */
    public function servePlaylist($playlist)
    {
        try {
            if (!Storage::disk('minio')->exists("hls/{$playlist}")) {
                return response('Playlist not found', 404);
            }

            $playlistContent = Storage::disk('minio')->get("hls/{$playlist}");
            $video = Video::where('hls_path', "hls/{$playlist}")->first();

            $playlistContent = preg_replace_callback(
                '/URI="([^"]+\.key)"/',
                function ($matches) use ($playlist, $video) {
                    $originalKeyFile = basename($matches[1]);

                    if ($video && !empty($video->encryption_keys)) {
                        $keys = $video->encryption_keys;
                        if (is_array($keys) && count($keys) > 0) {
                            $actualKeyFile = null;

                            if ($video->encryption_type === 'single') {
                                $actualKeyFile = $keys[0];
                            } else {
                                foreach ($keys as $keyFile) {
                                    if (str_contains($keyFile, $originalKeyFile) || $keyFile === $originalKeyFile) {
                                        $actualKeyFile = $keyFile;
                                        break;
                                    }
                                }
                                $actualKeyFile = $actualKeyFile ?: $originalKeyFile;
                            }

                            Log::info("Using key: {$actualKeyFile} for playlist: {$playlist}");
                            return 'URI="' . route('video.key', ['key' => $actualKeyFile]) . '"';
                        }
                    }

                    $keyPath = "keys/{$originalKeyFile}";
                    if (Storage::disk('minio')->exists($keyPath)) {
                        Log::info("Using fallback key: {$originalKeyFile} for playlist: {$playlist}");
                        return 'URI="' . route('video.key', ['key' => $originalKeyFile]) . '"';
                    }

                    Log::warning("Key file missing: {$keyPath} for playlist: {$playlist}");
                    return 'URI="' . route('video.key', ['key' => $originalKeyFile]) . '"';
                },
                $playlistContent
            );

            return response($playlistContent, 200, [
                'Content-Type' => 'application/vnd.apple.mpegurl',
                'Cache-Control' => 'no-cache'
            ]);
        } catch (\Exception $e) {
            Log::error('Error serving playlist: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error serving playlist: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Melayani kunci enkripsi dengan autentikasi/otorisasi
     */
    public function serveKey($key)
    {
        try {
            if (!auth()->check()) {
                return response('Unauthorized', 401);
            }

            $video = Video::whereJsonContains('encryption_keys', $key)
                ->orWhere('encryption_keys', 'like', '%"' . $key . '"%')
                ->first();

            if (!$video) {
                Log::warning("Video not found for key: {$key}");
                return response('Key not found', 404);
            }

            if ($video->user_id !== auth()->id() && !$video->is_public) {
                return response('Forbidden', 403);
            }

            if (!Storage::disk('minio')->exists("keys/{$key}")) {
                Log::warning("Key file not found in MinIO: keys/{$key}");
                return response('Key not found', 404);
            }

            $keyContent = Storage::disk('minio')->get("keys/{$key}");

            Log::info('Video key accessed', [
                'key' => $key,
                'video_id' => $video->id,
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return response($keyContent, 200, [
                'Content-Type' => 'application/octet-stream',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        } catch (\Exception $e) {
            Log::error('Error serving key: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error serving key: ' . $e->getMessage()
            ], 400);
        }
    }

    public function showPlayer($playlist = null)
    {
        $userVideos = auth()->check() ? auth()->user()->videos()->where('status', 'completed')->get() : collect();
        return view('video.player', compact('playlist', 'userVideos'));
    }

    public function exportLink($id)
    {
        $video = Video::findOrFail($id);
        if ($video->user_id !== auth()->id() && !$video->is_public) {
            abort(403);
        }
        $link = route('video.player', ['playlist' => basename((string) $video->hls_path)]);
        return response()->json([
            'success' => true,
            'link' => $link
        ]);
    }

    public function exportEmbed($id)
    {
        $video = Video::findOrFail($id);
        if ($video->user_id !== auth()->id() && !$video->is_public) {
            abort(403);
        }
        $playlist = route('video.player', ['playlist' => basename((string) $video->hls_path)]);
        $embedCode = '<iframe src="' . $playlist . '" width="640" height="360" frameborder="0" allowfullscreen></iframe>';
        return response()->json([
            'success' => true,
            'embed_code' => $embedCode
        ]);
    }

    public function generateExportLink(Request $request)
    {
        try {
            $request->validate([
                'video_id' => 'required|exists:videos,id',
                'allow_export' => 'boolean',
                'allow_embed' => 'boolean',
                'expires_in_days' => 'nullable|integer|min:1|max:365',
                'allowed_domains' => 'nullable|array',
                'allowed_domains.*' => 'string'
            ]);
            $video = Video::findOrFail($request->video_id);
            if ($video->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to video'
                ], 403);
            }
            if (!$video->export_token) {
                $video->generateExportToken();
            }

            $embedSettings = [];
            if ($request->has('allowed_domains') && !empty($request->allowed_domains)) {
                $embedSettings['allowed_domains'] = array_filter($request->allowed_domains);
            }
            $updateData = [
                'allow_export' => $request->boolean('allow_export', false),
                'allow_embed' => $request->boolean('allow_embed', false),
                'embed_settings' => $embedSettings
            ];
            if ($request->expires_in_days) {
                $updateData['export_expires_at'] = now()->addDays($request->expires_in_days);
            } else {
                $updateData['export_expires_at'] = null;
            }
            $video->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Export settings updated successfully',
                'data' => [
                    'export_url' => $video->getExportUrl(),
                    'embed_url' => $video->getEmbedUrl(),
                    'export_token' => $video->export_token,
                    'expires_at' => $video->export_expires_at ? $video->export_expires_at->toIso8601String() : null,
                    'embed_code' => $video->allow_embed ? '<iframe src="' . $video->getEmbedUrl() . '" width="800" height="450" frameborder="0" allowfullscreen></iframe>' : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating export link: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update title/description (inline modal form)
     */
    public function updateMeta(Request $request)
    {
        try {
            $data = $request->validate([
                'video_id' => 'required|exists:videos,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'is_public' => 'nullable|boolean',
            ]);

            $video = Video::findOrFail($data['video_id']);

            if ($video->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $video->title = trim($data['title']);
            $video->description = $data['description'] ?? null;
            if ($request->has('is_public')) {
                $video->is_public = (bool) $request->boolean('is_public');
            }
            $video->save();

            return response()->json([
                'success' => true,
                'message' => 'Updated successfully',
                'data' => [
                    'id' => $video->id,
                    'title' => $video->title,
                    'description' => $video->description,
                    'is_public' => (bool) $video->is_public,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function revokeExportAccess(Request $request)
    {
        try {
            $request->validate([
                'video_id' => 'required|exists:videos,id'
            ]);

            $video = Video::findOrFail($request->video_id);

            if ($video->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to video'
                ], 403);
            }

            $video->update([
                'export_token' => null,
                'allow_export' => false,
                'allow_embed' => false,
                'export_expires_at' => null,
                'embed_settings' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Export access revoked successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error revoking export access: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showExportVideo($token)
    {
        try {
            $video = Video::where('export_token', $token)->first();

            if (!$video || !$video->allow_export || ($video->export_expires_at && $video->export_expires_at->isPast())) {
                return view('video.export', [
                    'error' => 'Video Not Found',
                    'errorMessage' => 'The video you are looking for does not exist or has been removed.'
                ]);
            }

            if (!$video->isExportValid()) {
                return view('video.export', [
                    'error' => 'Access Expired',
                    'errorMessage' => 'The export link for this video has expired or been revoked.'
                ]);
            }

            $video->incrementViewCount();
            return view('video.export', compact('video'));
        } catch (\Exception $e) {
            return view('video.export', [
                'error' => 'Error Loading Video',
                'errorMessage' => 'An error occurred while loading the video. Please try again later.'
            ]);
        }
    }

    public function showEmbedVideo(Request $request, $token)
    {
        try {
            $video = Video::where('export_token', $token)->first();

            if (!$video) {
                return view('video.embed', [
                    'error' => 'Video Not Found',
                    'errorMessage' => 'The video you are looking for does not exist.'
                ]);
            }

            if (!$video->allow_embed || !$video->export_token || ($video->export_expires_at && $video->export_expires_at->isPast())) {
                return view('video.embed', [
                    'error' => 'Embed Not Allowed',
                    'errorMessage' => 'This video cannot be embedded or the embed access has expired.'
                ]);
            }

            $referer = $request->header('referer');
            if ($referer) {
                $domain = parse_url($referer, PHP_URL_HOST);
                if ($domain && !$video->isEmbedAllowedForDomain($domain)) {
                    return view('video.embed', [
                        'error' => 'Domain Not Allowed',
                        'errorMessage' => 'This video cannot be embedded on this domain.'
                    ]);
                }
            }

            $video->incrementViewCount();

            return view('video.embed', compact('video'));

        } catch (\Exception $e) {
            return view('video.embed', [
                'error' => 'Error Loading Video',
                'errorMessage' => 'An error occurred while loading the video. Please try again later.'
            ]);
        }
    }

    public function servePlaylistWithToken($token)
    {
        try {
            $video = Video::where('export_token', $token)->first();

            if (!$video) {
                if (str_contains($token, '.m3u8') || str_contains($token, 'video_')) {
                    $playlistFile = $token;
                    if (!str_ends_with($playlistFile, '.m3u8')) {
                        $playlistFile .= '.m3u8';
                    }

                    $video = Video::where('hls_path', "hls/{$playlistFile}")
                        ->orWhere('hls_path', $playlistFile)
                        ->first();
                }
            }

            if (!$video) {
                Log::warning("Video not found for token/filename: {$token}");
                return response('Video not found', 404);
            }

            if (!$video->export_token || (! $video->allow_export && ! $video->allow_embed)) {
                Log::warning("Video found but export/embed not allowed: {$video->id}");
                return response('Video not found or access expired', 404);
            }

            if ($video->export_expires_at && $video->export_expires_at->isPast()) {
                Log::warning("Video export token expired: {$video->id}");
                return response('Video not found or access expired', 404);
            }

            if (!Storage::disk('minio')->exists($video->hls_path)) {
                Log::error("Playlist file not found in MinIO: {$video->hls_path}");
                return response('Playlist not found', 404);
            }

            $playlistContent = Storage::disk('minio')->get($video->hls_path);

            $playlistContent = preg_replace_callback(
                '/URI="([^"]+\.key)"/',
                function ($matches) use ($video) {
                    $keyFile = basename($matches[1]);
                    return 'URI="' . route('video.key.token', [
                        'token' => $video->export_token,
                        'key' => $keyFile
                    ]) . '"';
                },
                $playlistContent
            );

            $playlistContent = preg_replace_callback(
                '/^([^#\s].+\.m3u8)$/m',
                function ($matches) use ($video) {
                    $segmentPlaylist = trim($matches[1]);
                    return route('video.segment.token', [
                        'token' => $video->export_token,
                        'filename' => $segmentPlaylist
                    ]);
                },
                $playlistContent
            );

            Log::info("Serving playlist for video: {$video->id}, token: {$video->export_token}");

            return response($playlistContent, 200, [
                'Content-Type' => 'application/vnd.apple.mpegurl',
                'Cache-Control' => 'no-cache',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type'
            ]);

        } catch (\Exception $e) {
            Log::error('Error serving playlist with token: ' . $e->getMessage(), [
                'token' => $token,
                'trace' => $e->getTraceAsString()
            ]);
            return response('Playlist not found', 404);
        }
    }

    public function serveSegmentPlaylist($token, $filename)
    {
        try {
            $video = Video::where('export_token', $token)->first();

            if (!$video || !$video->isExportValid()) {
                Log::warning("Video not found or export invalid for token: {$token}");
                return response('Video not found or access expired', 404);
            }

            $segmentPath = "hls/{$filename}";

            if (!Storage::disk('minio')->exists($segmentPath)) {
                Log::error("Segment playlist not found in MinIO: {$segmentPath}");
                return response('Playlist not found', 404);
            }

            $playlistContent = Storage::disk('minio')->get($segmentPath);

            $playlistContent = preg_replace_callback(
                '/URI="([^"]+\.key)"/',
                function ($matches) use ($video) {
                    $keyFile = basename($matches[1]);
                    return 'URI="' . route('video.key.token', [
                        'token' => $video->export_token,
                        'key' => $keyFile
                    ]) . '"';
                },
                $playlistContent
            );

            $playlistContent = preg_replace_callback(
                '/^([^#\s].+\.ts)$/m',
                function ($matches) use ($video) {
                    $segmentFile = trim($matches[1]);
                    return route('video.segment.file', [
                        'token' => $video->export_token,
                        'filename' => $segmentFile
                    ]);
                },
                $playlistContent
            );

            Log::info("Serving segment playlist: {$filename} for video: {$video->id}");

            return response($playlistContent, 200, [
                'Content-Type' => 'application/vnd.apple.mpegurl',
                'Cache-Control' => 'no-cache',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type'
            ]);

        } catch (\Exception $e) {
            Log::error('Error serving segment playlist: ' . $e->getMessage(), [
                'token' => $token,
                'filename' => $filename,
                'trace' => $e->getTraceAsString()
            ]);
            return response('Playlist not found', 404);
        }
    }

    public function serveKeyWithToken($token, $key)
    {
        try {
            $video = Video::where('export_token', $token)->first();

            if (!$video || !$video->isExportValid()) {
                return response('Unauthorized', 401);
            }

            $encryptionKeys = $video->encryption_keys ?? [];
            if (!in_array($key, $encryptionKeys)) {
                return response('Key not found', 404);
            }

            $keyPath = "keys/{$key}";
            if (!Storage::disk('minio')->exists($keyPath)) {
                return response('Key not found', 404);
            }

            $keyContent = Storage::disk('minio')->get($keyPath);

            return response($keyContent, 200, [
                'Content-Type' => 'application/octet-stream',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type'
            ]);

        } catch (\Exception $e) {
            return response('Key not found', 404);
        }
    }

    public function serveSegmentFile($token, $filename)
    {
        try {
            $video = Video::where('export_token', $token)->first();

            if (!$video || !$video->isExportValid()) {
                Log::warning("Unauthorized segment access or invalid token: {$token}");
                return response('Segment not found', 404);
            }

            $segmentPath = "hls/{$filename}";

            if (!Storage::disk('minio')->exists($segmentPath)) {
                Log::error("Segment file not found in MinIO: {$segmentPath}");
                return response('Segment not found', 404);
            }

            $stream = Storage::disk('minio')->get($segmentPath);

            return response($stream, 200, [
                'Content-Type' => 'video/MP2T',
                'Cache-Control' => 'no-cache',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type'
            ]);

        } catch (\Exception $e) {
            Log::error('Error serving segment file: ' . $e->getMessage(), [
                'token' => $token,
                'filename' => $filename
            ]);
            return response('Segment not found', 404);
        }
    }

    public function recordAnalytics(Request $request, $token)
    {
        try {
            $video = Video::where('export_token', $token)->first();

            if (!$video) {
                return response()->json(['success' => false], 404);
            }

            $event = $request->input('event');
            $referrer = $request->input('referrer', 'direct');
            $userAgent = $request->input('user_agent', $request->userAgent());

            Log::info('Video analytics event', [
                'video_id' => $video->id,
                'event' => $event,
                'referrer' => $referrer,
                'user_agent' => $userAgent,
                'ip' => $request->ip(),
                'timestamp' => now()
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }
}
