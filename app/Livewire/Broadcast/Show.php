<?php
namespace App\Livewire\Broadcast;

use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use App\Models\Broadcast as BroadcastModel;

class Show extends Component
{
    public BroadcastModel $broadcast;
    public array $remote = [];
    public ?string $error = null;
    public array $stats = [];

    public function mount(int $id): void
    {
        $this->broadcast = BroadcastModel::with('channel')->findOrFail($id);

        // Only fetch remote details for WA Unofficial and if external_id exists
        if ($this->broadcast->channel && $this->broadcast->channel->auth_type === 'wa-unofficial' && $this->broadcast->external_id) {
            $base = rtrim((string) $this->broadcast->channel->base_url, '/');
            $headers = ['accept' => 'application/json'];
            if ($this->broadcast->channel->headers_key && $this->broadcast->channel->headers_value) {
                $headers[$this->broadcast->channel->headers_key] = $this->broadcast->channel->headers_value;
            }
            try {
                $resp = Http::withHeaders($headers)->get($base . '/broadcasts/' . urlencode($this->broadcast->external_id));
                if ($resp->successful()) {
                    $this->remote = (array) $resp->json();
                    $stats = Arr::get($this->remote, 'data.stats') ?? Arr::get($this->remote, 'stats');
                    if (is_array($stats)) {
                        $this->stats = [
                            'queued' => (int) ($stats['queued'] ?? 0),
                            'processing' => (int) ($stats['processing'] ?? 0),
                            'sent' => (int) ($stats['sent'] ?? 0),
                            'failed' => (int) ($stats['failed'] ?? 0),
                        ];
                    }
                } else {
                    $this->error = 'HTTP ' . $resp->status();
                }
            } catch (\Throwable $e) {
                $this->error = $e->getMessage();
            }
        }
    }

    public function render()
    {
        return view('livewire.broadcast.show')
            ->layout('components.layouts.app', [
                'title' => __('Broadcast Detail'),
                'breadcrumbs' => [
                    ['label' => __('Dashboard'), 'url' => route('dashboard')],
                    ['label' => __('Broadcast History'), 'url' => route('broadcast.index')],
                    ['label' => __('Detail')],
                ],
            ]);
    }
}
