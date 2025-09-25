<?php
namespace App\Livewire\NotificationChannel;

use Livewire\Component;
use App\Models\NotificationChannel;

class Delete extends Component
{
    public $channelId;

    protected $listeners = ['deleteChannel' => 'delete'];

    public function delete($id)
    {
        NotificationChannel::findOrFail($id)->delete();
        session()->flash('success', 'Channel deleted successfully.');
        return redirect()->route('notification-channel.index');
    }

    public function render()
    {
        return '';
    }
}
