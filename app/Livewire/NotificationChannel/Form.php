<?php
namespace App\Livewire\NotificationChannel;

use Livewire\Component;
use App\Models\NotificationChannel;
use Illuminate\Support\Facades\Crypt;

class Form extends Component
{
    public $channelId;
    public $name;
    public $base_url;
    public $headers_key;
    public $headers_value;
    public $auth_type;
    public $type; // derived channel type (e.g., wa_unoffical)
    public $n8n_username;
    public $n8n_password;

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'base_url' => ['required', 'string', 'max:2048'],
            'auth_type' => ['nullable', 'string', 'in:header,wa-unofficial'],
            'headers_key' => ['nullable', 'string', 'max:255'],
            'headers_value' => ['nullable', 'string'],
        ];

        if (in_array($this->auth_type, ['header', 'wa-unofficial'], true) && $this->type !== 'n8n') {
            $rules['headers_key'] = ['required', 'string', 'max:255'];
            $rules['headers_value'] = ['required', 'string'];
        }

        if ($this->type === 'n8n') {
            $rules['n8n_username'] = ['nullable', 'string', 'max:255'];
            $rules['n8n_password'] = ['nullable', 'string'];
        }

        return $rules;
    }

    public function mount($id = null)
    {
        if ($id) {
            $channel = NotificationChannel::findOrFail($id);
            $this->channelId = $channel->id;
            $this->name = $channel->name;
            $this->base_url = $channel->base_url;
            $this->headers_key = $channel->headers_key;
            $this->headers_value = $channel->headers_value;
            $this->auth_type = $channel->auth_type;
            $this->type = $channel->type;
            $this->n8n_username = $channel->n8n_username;
            // do not prefill password
        } else {
            $type = request()->query('type');
            if (in_array($type, ['header', 'wa-unofficial', 'n8n'], true)) {
                $this->auth_type = $type;
                if ($type === 'wa-unofficial' && empty($this->name)) {
                    $this->name = 'WA Unofficial';
                }
                if ($type === 'n8n') {
                    $this->name = $this->name ?: 'N8N';
                    // For N8N we primarily use header auth with X-N8N-API-Key
                    $this->auth_type = 'header';
                    $this->headers_key = $this->headers_key ?: 'X-N8N-API-Key';
                }
            }
            // Map to new type column
            if ($type === 'n8n') {
                $this->type = 'n8n';
            } else {
                $this->type = $this->auth_type === 'wa-unofficial' ? 'wa_unoffical' : 'custom';
            }
        }
    }

    public function save()
    {
        $this->validate();
        $type = $this->type ?: ($this->auth_type === 'wa-unofficial' ? 'wa_unoffical' : 'custom');
        $attrs = [
            'user_id' => auth()->id(),
            'type' => $type,
            'name' => $this->name,
            'base_url' => $this->base_url,
            'headers_key' => $this->headers_key,
            'headers_value' => $this->headers_value,
            'auth_type' => $this->auth_type,
        ];
        if ($type === 'n8n') {
            $attrs['n8n_username'] = $this->n8n_username;
            if (!empty($this->n8n_password)) {
                $attrs['n8n_password'] = Crypt::encryptString($this->n8n_password);
            }
        }
        if ($this->channelId) {
            NotificationChannel::where('id', $this->channelId)
                ->where('user_id', auth()->id())
                ->update($attrs);
        } else {
            // One per user per type
            NotificationChannel::updateOrCreate(
                ['user_id' => auth()->id(), 'type' => $type],
                $attrs
            );
        }
        return redirect()->route('notification-channel.index');
    }

    public function render()
    {
        $isEdit = (bool) $this->channelId;
        return view('livewire.notification-channel.form')
            ->layout('components.layouts.app', [
                'title' => $isEdit ? __('Edit Notification Channel') : __('Tambah Notification Channel'),
                'breadcrumbs' => [
                    ['label' => __('Dashboard'), 'url' => route('dashboard')],
                    ['label' => __('Notification Setting'), 'url' => route('notification-channel.index')],
                    ['label' => $isEdit ? __('Edit') : __('Tambah')],
                ],
            ]);
    }
}
