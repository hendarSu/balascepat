<?php
namespace App\Livewire\Wa;

use Livewire\Component;
use App\Models\NotificationChannel;
use App\Models\AutomationConfig;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class Automation extends Component
{
    public ?NotificationChannel $channel = null;
    public string $baseUrl = '';
    public array $sessions = [];
    public ?string $selectedClientId = null;

    // Local DB config
    public ?int $configId = null;
    public string $webhookUrl = '';
    public string $secret = '';

    // Remote fetched
    public array $remoteConfig = [];
    public ?string $error = null;

    public function mount(): void
    {
        $this->channel = NotificationChannel::where('auth_type', 'wa-unofficial')->first();
        if ($this->channel) {
            $this->baseUrl = rtrim((string) $this->channel->base_url, '/');
            $this->loadSessions();
        }
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

    private function isAssoc(mixed $value): bool
    {
        return is_array($value) && array_keys($value) !== range(0, count($value) - 1);
    }

    private function collectSessionsFrom(mixed $value): array
    {
        $found = [];
        if ($this->isAssoc($value)) {
            if (array_key_exists('data', $value)) return $this->collectSessionsFrom($value['data']);
            if (array_key_exists('clientId', $value) || array_key_exists('id', $value) || array_key_exists('status', $value)) {
                $found[] = $value;
            }
            foreach ($value as $v) { $found = array_merge($found, $this->collectSessionsFrom($v)); }
            return $found;
        }
        if (is_array($value)) {
            foreach ($value as $v) { $found = array_merge($found, $this->collectSessionsFrom($v)); }
        }
        return $found;
    }

    public function loadSessions(): void
    {
        if (!$this->channel) return;
        $this->sessions = [];
        try {
            $resp = Http::withHeaders($this->headers())->get($this->baseUrl . '/accounts');
            if ($resp->successful()) {
                $payload = $resp->json();
                $all = $this->collectSessionsFrom($payload);
                // Keep only active sessions
                $this->sessions = array_values(array_filter($all, function($s){
                    if (!is_array($s)) return false;
                    $status = strtolower((string)($s['status'] ?? $s['state'] ?? $s['connection_state'] ?? ''));
                    return in_array($status, ['connected','open','ready','authenticated']);
                }));
            } else {
                $this->error = __('Gagal memuat sesi: :code', ['code' => $resp->status()]);
            }
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function selectClient(string $clientId): void
    {
        $this->selectedClientId = $clientId;
        $this->loadLocalConfig();
        $this->fetchRemoteConfig();
    }

    public function resetSelection(): void
    {
        $this->selectedClientId = null;
        $this->configId = null;
        $this->webhookUrl = '';
        $this->secret = '';
        $this->remoteConfig = [];
        $this->error = null;
    }

    private function loadLocalConfig(): void
    {
        if (!$this->channel || !$this->selectedClientId) return;
        $cfg = AutomationConfig::where('channel_id', $this->channel->id)
            ->where('client_id', $this->selectedClientId)
            ->first();
        $this->configId = $cfg?->id;
        $this->webhookUrl = $cfg?->webhook_url ?? '';
        $this->secret = $cfg?->secret ?? '';
    }

    public function fetchRemoteConfig(): void
    {
        $this->remoteConfig = [];
        $this->error = null;
        if (!$this->channel || !$this->selectedClientId) return;
        try {
            $resp = Http::withHeaders($this->headers())
                ->get($this->baseUrl . '/automation/config/' . urlencode($this->selectedClientId));
            if ($resp->successful()) {
                $this->remoteConfig = (array) $resp->json();
            } else {
                $this->error = 'HTTP ' . $resp->status();
            }
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    protected function rules(): array
    {
        return [
            'selectedClientId' => ['required', 'string'],
            'webhookUrl' => ['required', 'url'],
            'secret' => ['required', 'string'],
        ];
    }

    public function saveConfig(): void
    {
        $this->validate();
        if (!$this->channel) return;

        // Remote create/update
        $payload = [
            'clientId' => $this->selectedClientId,
            'webhookUrl' => $this->webhookUrl,
            'secret' => $this->secret,
        ];
        try {
            // According to note: POST is at 3030; still use channel baseUrl as source of truth
            $base = $this->baseUrl ?: 'http://localhost:3030';
            $resp = Http::withHeaders($this->headers())
                ->post(rtrim($base, '/') . '/automation/config', $payload);
            $respJson = $resp->json();
            if ($resp->failed()) {
                session()->flash('error', __('Gagal menyimpan ke server: :code', ['code' => $resp->status()]));
            }
        } catch (\Throwable $e) {
            $respJson = ['error' => $e->getMessage()];
            session()->flash('error', __('Gagal terhubung: :msg', ['msg' => $e->getMessage()]));
        }

        // Local upsert
        $cfg = AutomationConfig::updateOrCreate(
            ['id' => $this->configId],
            [
                'channel_id' => $this->channel->id,
                'client_id' => $this->selectedClientId,
                'webhook_url' => $this->webhookUrl,
                'secret' => $this->secret,
                'last_response' => $respJson ?? null,
                'synced_at' => now(),
            ]
        );
        $this->configId = $cfg->id;
        session()->flash('success', __('Konfigurasi tersimpan.'));
        $this->fetchRemoteConfig();
    }

    public function render()
    {
        return view('livewire.wa.automation')
            ->layout('components.layouts.app', [
                'title' => __('WA Unofficial — Automation Config'),
                'breadcrumbs' => [
                    ['label' => __('Dashboard'), 'url' => route('dashboard')],
                    ['label' => __('WA Unofficial')],
                    ['label' => __('Automation Config')],
                ],
            ]);
    }
}
