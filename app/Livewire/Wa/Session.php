<?php
namespace App\Livewire\Wa;

use App\Models\NotificationChannel;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class Session extends Component
{
    public ?NotificationChannel $channel = null;
    public string $baseUrl = '';
    public array $sessions = [];
    public ?string $phone = null;
    public array $qrs = []; // [phone => ['type' => 'image'|'text', 'data' => string]]
    public ?string $selectedPhone = null;
    public bool $qrModalOpen = false;

    public function mount(): void
    {
        $this->channel = NotificationChannel::where('auth_type', 'wa-unofficial')->first();
        if ($this->channel) {
            $this->baseUrl = rtrim((string) $this->channel->base_url, '/');
            $this->refreshSessions(silent: true);
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
            // Common API shape: { data: [...], message: "..." }
            if (array_key_exists('data', $value)) {
                return $this->collectSessionsFrom($value['data']);
            }
            // Candidate object – has any typical keys
            if (array_key_exists('clientId', $value) || array_key_exists('id', $value) || array_key_exists('status', $value)) {
                $found[] = $value;
            }
            // Also check nested values
            foreach ($value as $v) {
                $found = array_merge($found, $this->collectSessionsFrom($v));
            }
            return $found;
        }
        if (is_array($value)) {
            foreach ($value as $v) {
                $found = array_merge($found, $this->collectSessionsFrom($v));
            }
        }
        return $found;
    }

    private function extractQrFrom(mixed $value): ?string
    {
        if (is_string($value)) {
            $val = trim($value);
            if (Str::startsWith($val, 'data:image')) return $val;
            // treat any long-ish base64 as image
            if (strlen($val) > 50 && !str_contains($val, '{') && !str_contains($val, '[')) {
                return 'data:image/png;base64,' . $val;
            }
        }
        if ($this->isAssoc($value)) {
            if (array_key_exists('data', $value)) {
                $qr = $this->extractQrFrom($value['data']);
                if ($qr) return $qr;
            }
            foreach (['qr', 'image'] as $key) {
                if (array_key_exists($key, $value)) {
                    $qr = $this->extractQrFrom($value[$key]);
                    if ($qr) return $qr;
                }
            }
            foreach ($value as $v) {
                $qr = $this->extractQrFrom($v);
                if ($qr) return $qr;
            }
        }
        if (is_array($value)) {
            foreach ($value as $v) {
                $qr = $this->extractQrFrom($v);
                if ($qr) return $qr;
            }
        }
        return null;
    }

    private function extractTextFrom(mixed $value): ?string
    {
        if (is_string($value)) {
            $val = trim($value);
            if ($val !== '') return $val;
        }
        if ($this->isAssoc($value)) {
            if (array_key_exists('data', $value)) {
                $txt = $this->extractTextFrom($value['data']);
                if ($txt) return $txt;
            }
            foreach (['text', 'code', 'token', 'qrText'] as $key) {
                if (array_key_exists($key, $value)) {
                    $txt = $this->extractTextFrom($value[$key]);
                    if ($txt) return $txt;
                }
            }
            foreach ($value as $v) {
                $txt = $this->extractTextFrom($v);
                if ($txt) return $txt;
            }
        }
        if (is_array($value)) {
            foreach ($value as $v) {
                $txt = $this->extractTextFrom($v);
                if ($txt) return $txt;
            }
        }
        return null;
    }

    public function refreshSessions(bool $silent = false): void
    {
        if (!$this->channel) return;
        try {
            $resp = Http::withHeaders($this->headers())->get($this->baseUrl . '/accounts');
            if ($resp->successful()) {
                $payload = $resp->json();
                // Update baseUrl if API provides it
                $apiBase = Arr::get($payload, 'data.baseUrl') ?? Arr::get($payload, 'baseUrl');
                if (is_string($apiBase) && $apiBase) {
                    $this->baseUrl = rtrim($apiBase, '/');
                }
                $this->sessions = $this->collectSessionsFrom($payload);
                if (!$silent) {
                    $msg = Arr::get($payload, 'message');
                    session()->flash('success', $msg ?: __('Daftar sesi diperbarui.'));
                }
            } else {
                session()->flash('error', __('Gagal memuat sesi: :code', ['code' => $resp->status()]));
            }
        } catch (\Throwable $e) {
            session()->flash('error', __('Gagal terhubung: :msg', ['msg' => $e->getMessage()]));
        }
    }

    public function createSession(): void
    {
        $this->validate(['phone' => ['required', 'string']]);
        if (!$this->channel) return;
        try {
            $resp = Http::withHeaders($this->headers())
                ->post($this->baseUrl . "/accounts/{$this->phone}/start");
            if ($resp->successful()) {
                $payload = $resp->json();
                $msg = Arr::get($payload, 'message');
                session()->flash('success', $msg ?: __('Sesi dibuat.'));
                $this->refreshSessions(silent: true);
            } else {
                session()->flash('error', __('Gagal membuat sesi: :code', ['code' => $resp->status()]));
            }
        } catch (\Throwable $e) {
            session()->flash('error', __('Gagal terhubung: :msg', ['msg' => $e->getMessage()]));
        }
    }

    public function reconnect(string $phone): void
    {
        if (!$this->channel) return;
        try {
            $resp = Http::withHeaders($this->headers())
                ->post($this->baseUrl . "/accounts/{$phone}/reconnect");
            if ($resp->successful()) {
                $payload = $resp->json();
                $msg = Arr::get($payload, 'message');
                session()->flash('success', $msg ?: __('Reconnect berhasil.'));
                $this->refreshSessions(silent: true);
            } else {
                session()->flash('error', __('Gagal reconnect: :code', ['code' => $resp->status()]));
            }
        } catch (\Throwable $e) {
            session()->flash('error', __('Gagal terhubung: :msg', ['msg' => $e->getMessage()]));
        }
    }

    public function fetchQr(string $phone): void
    {
        if (!$this->channel) return;
        try {
            $resp = Http::withHeaders($this->headers())
                ->get($this->baseUrl . "/accounts/{$phone}/qr");
            if ($resp->successful()) {
                $payload = $resp->json();
                $qr = $this->extractQrFrom($payload);
                if ($qr) {
                    $this->qrs[$phone] = ['type' => 'image', 'data' => $qr];
                } else {
                    $text = $this->extractTextFrom($payload);
                    if ($text) {
                        $this->qrs[$phone] = ['type' => 'text', 'data' => $text];
                    } else {
                        session()->flash('error', __('Format QR tidak dikenali.'));
                    }
                }
            } else {
                session()->flash('error', __('Gagal ambil QR: :code', ['code' => $resp->status()]));
            }
        } catch (\Throwable $e) {
            session()->flash('error', __('Gagal terhubung: :msg', ['msg' => $e->getMessage()]));
        }
    }

    public function showQr(string $phone): void
    {
        $this->selectedPhone = $phone;
        $this->fetchQr($phone);
        // Open modal (two ways: bind :show and fire event for JS hooks)
        $this->qrModalOpen = true;
        $this->dispatch('open-modal', 'qr-preview');
    }

    public function render()
    {
        return view('livewire.wa.session')
            ->layout('components.layouts.app', [
                'title' => __('WA Unofficial — Session'),
                'breadcrumbs' => [
                    ['label' => __('Dashboard'), 'url' => route('dashboard')],
                    ['label' => __('WA Unofficial')],
                    ['label' => __('Session')],
                ],
            ]);
    }
}
