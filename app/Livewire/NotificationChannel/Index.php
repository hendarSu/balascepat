<?php
namespace App\Livewire\NotificationChannel;

use Livewire\Component;
use App\Models\NotificationChannel;

class Index extends Component
{
    public $channels;

    public function mount()
    {
        $this->channels = NotificationChannel::all();
    }

    public function delete(int $id): void
    {
        $channel = NotificationChannel::findOrFail($id);
        $channel->delete();

        session()->flash('success', __('Channel deleted successfully.'));

        // Refresh the list without a full redirect
        $this->channels = NotificationChannel::all();
    }

    public function render()
    {
        return view('livewire.notification-channel.index')
            ->layout('components.layouts.app', [
                'title' => __('Notification Channels'),
                'breadcrumbs' => [
                    ['label' => __('Dashboard'), 'url' => route('dashboard')],
                    ['label' => __('Notification Setting')],
                ],
            ]);
    }
}
