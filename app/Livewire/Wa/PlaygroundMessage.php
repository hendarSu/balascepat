<?php
namespace App\Livewire\Wa;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Models\NotificationChannel;

class PlaygroundMessage extends Component
{
    public ?NotificationChannel $channel = null;
    public string $baseUrl = '';
    public array $channels = [];
    public ?int $channel_id = null;
    public array $accounts = [];
    public array $clients = [];
    public ?string $selectedClient = null; // format: "{channel_id}::{clientId}"

    public string $clientId = '';
    public string $to = '';
    public string $text = '';
    public int $maxAttempts = 2;

    public ?array $response = null;
    public ?string $error = null;

    public function mount(): void
    {
        $this->channels = NotificationChannel::forCurrentUser()
            ->ofType('wa_unoffical')
            ->orderBy('name')->get(['id','name','base_url','headers_key','headers_value','type'])->toArray();
        $this->loadClients();
        if (!empty($this->clients)) {
            $this->selectedClient = $this->clients[0]['value'] ?? null;
            $this->applySelectedClient();
        }
    }

    protected function rules(): array
    {
        return [
            'clientId' => ['required', 'string'],
            'to' => ['required', 'string'],
            'text' => ['required', 'string'],
            'maxAttempts' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }

    protected function headers(): array
    {
        if (!$this->channel) return [];
        $headers = ['accept' => 'application/json'];
        if ($this->channel->headers_key && $this->channel->headers_value) {
            $headers[$this->channel->headers_key] = $this->channel->headers_value;
        }
        return $headers;
    }

    public function updatedSelectedClient(): void
    {
        $this->applySelectedClient();
    }

    public function loadClients(): void
    {
        $this->clients = [];
        foreach ($this->channels as $ch) {
            $this->channel = NotificationChannel::forCurrentUser()->find($ch['id']);
            $this->baseUrl = rtrim((string) ($this->channel->base_url ?? ''), '/');
            if (!$this->channel || !$this->baseUrl) continue;
            try {
                $resp = Http::withHeaders($this->headers())->get($this->baseUrl . '/accounts');
                if ($resp->failed()) continue;
                $payload = $resp->json();
                // collect accounts (dedup by id)
                $items = [];
                $stack = [$payload];
                while ($stack) {
                    $v = array_pop($stack);
                    if (is_array($v)) {
                        $isAssoc = array_keys($v) !== range(0, count($v)-1);
                        if ($isAssoc) {
                            if (array_key_exists('data', $v)) { $stack[] = $v['data']; }
                            $id = $v['clientId'] ?? $v['phone'] ?? $v['session'] ?? ($v['id'] ?? null);
                            $status = $v['status'] ?? $v['state'] ?? $v['connection_state'] ?? null;
                            if ($id !== null) { $items[(string)$id] = ['id' => (string)$id, 'status' => (string)$status]; }
                            // Push nested values except 'data' to avoid double traversal
                            foreach ($v as $key => $vv) { if ($key !== 'data') { $stack[] = $vv; } }
                        } else { foreach ($v as $vv) { $stack[] = $vv; } }
                    }
                }
                $active = array_values(array_filter(array_values($items), function($s){
                    $st = strtolower((string)($s['status'] ?? ''));
                    return in_array($st, ['connected','open','ready','authenticated']);
                }));
                foreach ($active as $s) {
                    $value = $ch['id'] . '::' . $s['id'];
                    $label = ($ch['name'] ?? 'Channel') . ' — ' . $s['id'] . ($s['status'] ? ' — '.$s['status'] : '');
                    $this->clients[] = [
                        'value' => $value,
                        'label' => $label,
                        'channel_id' => $ch['id'],
                        'client_id' => $s['id'],
                    ];
                }
            } catch (\Throwable $e) {
                // ignore per channel
            }
        }
        // reset to a sane state for headers/baseUrl
        $this->channel = null;
        $this->baseUrl = '';
    }

    private function applySelectedClient(): void
    {
        $this->channel = null; $this->channel_id = null; $this->clientId = ''; $this->baseUrl = '';
        if (!$this->selectedClient) return;
        [$cid, $client] = array_pad(explode('::', $this->selectedClient, 2), 2, null);
        if (!$cid || !$client) return;
        $this->channel_id = (int) $cid;
        $this->channel = NotificationChannel::forCurrentUser()->find($this->channel_id);
        if ($this->channel) {
            $this->baseUrl = rtrim((string) $this->channel->base_url, '/');
            $this->clientId = $client;
        }
    }

    public function send(): void
    {
        $this->validate();
        if (!$this->channel) {
            $this->error = __('Channel WA Unofficial belum dikonfigurasi.');
            return;
        }
        $this->response = null;
        $this->error = null;
        try {
            $payload = [
                'clientId' => $this->clientId,
                'to' => $this->to,
                'text' => $this->text,
                'maxAttempts' => $this->maxAttempts ?: 1,
            ];
            $resp = Http::withHeaders($this->headers())
                ->post($this->baseUrl . '/messages', $payload);
            if ($resp->successful()) {
                $this->response = (array) $resp->json();
                session()->flash('success', __('Pesan dikirim.'));
            } else {
                $this->error = 'HTTP ' . $resp->status();
            }
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.wa.playground-message')
            ->layout('components.layouts.app', [
                'title' => __('WA Unofficial — Playground Message'),
                'breadcrumbs' => [
                    ['label' => __('Dashboard'), 'url' => route('dashboard')],
                    ['label' => __('WA Unofficial')],
                    ['label' => __('Playground Message')],
                ],
            ]);
    }
}
