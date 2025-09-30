<?php
namespace App\Livewire\Broadcast;

use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use App\Models\NotificationChannel;
use App\Models\CustomerGroup;
use App\Models\Customer;
use App\Models\Broadcast;

class Form extends Component
{
    public array $channels = [];
    public array $groups = [];

    public ?int $channel_id = null;
    public ?int $group_id = null;
    public ?string $clientId = null; // account for WA Unofficial

    public string $name = '';
    public string $text = '';

    public array $accounts = []; // fetched accounts for selected channel

    public function mount(): void
    {
        $this->channels = NotificationChannel::forCurrentUser()
            ->orderBy('name')->get(['id','name','auth_type','base_url','headers_key','headers_value','type'])->toArray();
        $this->groups = CustomerGroup::orderBy('name')->get(['id','name'])->toArray();
    }

    public function updatedChannelId(): void
    {
        $this->loadAccountsForChannel();
    }

    private function currentChannel(): ?NotificationChannel
    {
        return $this->channel_id ? NotificationChannel::forCurrentUser()->find($this->channel_id) : null;
    }

    private function headersFor(?NotificationChannel $ch): array
    {
        if (!$ch) return [];
        $headers = ['accept' => 'application/json'];
        if ($ch->headers_key && $ch->headers_value) {
            $headers[$ch->headers_key] = $ch->headers_value;
        }
        return $headers;
    }

    private function collectAccountsFrom(mixed $value): array
    {
        $found = [];
        $isAssoc = is_array($value) && array_keys($value) !== range(0, count($value) - 1);
        if ($isAssoc) {
            if (array_key_exists('data', $value)) return $this->collectAccountsFrom($value['data']);
            $id = $value['clientId'] ?? $value['phone'] ?? $value['session'] ?? ($value['id'] ?? null);
            if ($id !== null) $found[] = $value;
            foreach ($value as $v) $found = array_merge($found, $this->collectAccountsFrom($v));
            return $found;
        }
        if (is_array($value)) {
            foreach ($value as $v) $found = array_merge($found, $this->collectAccountsFrom($v));
        }
        return $found;
    }

    public function loadAccountsForChannel(): void
    {
        $this->accounts = [];
        $this->clientId = null;
        $ch = $this->currentChannel();
        if (!$ch) return;
        if ($ch->type !== 'wa_unoffical') return; // only fetch for WA
        $base = rtrim((string) $ch->base_url, '/');
        try {
            $resp = Http::withHeaders($this->headersFor($ch))->get($base . '/accounts');
            if ($resp->successful()) {
                $payload = $resp->json();
                $items = $this->collectAccountsFrom($payload);
                $this->accounts = array_values(array_map(function ($s) {
                    $id = $s['clientId'] ?? $s['phone'] ?? $s['session'] ?? ($s['id'] ?? '');
                    $status = $s['status'] ?? $s['state'] ?? $s['connection_state'] ?? '';
                    return [
                        'id' => is_string($id) ? $id : (string) $id,
                        'label' => trim(($id ? $id : 'Unknown') . ($status ? " — $status" : '')),
                    ];
                }, $items));
            }
        } catch (\Throwable $e) {
            // ignore, accounts remain empty
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'text' => ['required', 'string'],
            'channel_id' => ['required', 'integer', 'exists:notification_channels,id'],
            'group_id' => ['required', 'integer', 'exists:customer_groups,id'],
            'clientId' => ['nullable', 'string'],
        ];
    }

    public function send(): void
    {
        $this->validate();
        // Preserve original values for history before any reset
        $originalName = $this->name;
        $originalText = $this->text;
        $ch = $this->currentChannel();
        if (!$ch) {
            session()->flash('error', __('Channel tidak ditemukan.'));
            return;
        }

        $recipients = Customer::where('customer_group_id', $this->group_id)->pluck('phone')->filter()->values()->all();
        if (empty($recipients)) {
            session()->flash('error', __('Tidak ada nomor pada group terpilih.'));
            return;
        }

        $payload = null; $statusOk = false; $remoteId = null;
        if ($ch->type === 'wa_unoffical') {
            if (!$this->clientId) {
                session()->flash('error', __('Pilih akun (clientId) untuk WA Unofficial.'));
                return;
            }
            $base = rtrim((string) $ch->base_url, '/') ?: 'http://localhost:3030';
            try {
                $resp = Http::withHeaders($this->headersFor($ch))->post($base . '/broadcasts', [
                    'clientId' => $this->clientId,
                    'name' => $originalName,
                    'text' => $originalText,
                    'recipients' => array_values($recipients),
                ]);
                $payload = $resp->json();
                if ($resp->successful()) {
                    $statusOk = true;
                    // Try to capture returned broadcast id
                    $remoteId = Arr::get($payload, 'data.id')
                        ?? Arr::get($payload, 'id')
                        ?? Arr::get($payload, 'broadcast.id');
                    session()->flash('success', __('Broadcast dikirim.'));
                } else {
                    session()->flash('error', __('Gagal kirim: :code', ['code' => $resp->status()]));
                }
            } catch (\Throwable $e) {
                session()->flash('error', __('Gagal terhubung: :msg', ['msg' => $e->getMessage()]));
            }
        } else {
            session()->flash('error', __('Jenis channel belum didukung untuk broadcast.'));
        }

        // Simpan history apapun hasilnya
        Broadcast::create([
            'name' => $originalName ?: '—',
            'text' => $originalText,
            'channel_id' => $ch->id,
            'group_id' => $this->group_id,
            'client_id' => $this->clientId,
            'external_id' => $remoteId,
            'recipients' => $recipients,
            'recipients_count' => count($recipients),
            'response' => $payload,
            'sent_at' => now(),
        ]);

        // Reset form fields after storing history
        $this->name = '';
        $this->text = '';
    }

    public function render()
    {
        return view('livewire.broadcast.form')
            ->layout('components.layouts.app', [
                'title' => __('Broadcast'),
                'breadcrumbs' => [
                    ['label' => __('Dashboard'), 'url' => route('dashboard')],
                    ['label' => __('Broadcast')],
                ],
            ]);
    }
}
