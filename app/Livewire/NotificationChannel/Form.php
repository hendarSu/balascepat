<?php
namespace App\Livewire\NotificationChannel;

use Livewire\Component;
use App\Models\NotificationChannel;

class Form extends Component
{
    public $channelId;
    public $name;
    public $base_url;
    public $headers_key;
    public $headers_value;
    public $auth_type;
    public $type; // derived channel type (e.g., wa_unoffical)

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'base_url' => ['required', 'string', 'max:2048'],
            'auth_type' => ['nullable', 'string', 'in:header,wa-unofficial'],
            'headers_key' => ['nullable', 'string', 'max:255'],
            'headers_value' => ['nullable', 'string'],
        ];

        if (in_array($this->auth_type, ['header', 'wa-unofficial'], true)) {
            $rules['headers_key'] = ['required', 'string', 'max:255'];
            $rules['headers_value'] = ['required', 'string'];
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
        } else {
            $type = request()->query('type');
            if (in_array($type, ['header', 'wa-unofficial'], true)) {
                $this->auth_type = $type;
                if ($type === 'wa-unofficial' && empty($this->name)) {
                    $this->name = 'WA Unofficial';
                }
            }
            // Map to new type column
            $this->type = $this->auth_type === 'wa-unofficial' ? 'wa_unoffical' : 'custom';
        }
    }

    public function save()
    {
        $this->validate();
        $type = $this->auth_type === 'wa-unofficial' ? 'wa_unoffical' : ($this->type ?: 'custom');
        $attrs = [
            'user_id' => auth()->id(),
            'type' => $type,
            'name' => $this->name,
            'base_url' => $this->base_url,
            'headers_key' => $this->headers_key,
            'headers_value' => $this->headers_value,
            'auth_type' => $this->auth_type,
        ];
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
