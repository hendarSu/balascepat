<?php
namespace App\Livewire\N8n;

use Livewire\Component;
use App\Models\NotificationChannel;

class Show extends Component
{
    public ?NotificationChannel $channel = null;

    public function mount(): void
    {
        $this->channel = NotificationChannel::forCurrentUser()->ofType('n8n')->first();
    }

    public function render()
    {
        return view('livewire.n8n.show')
            ->layout('components.layouts.app', [
                'title' => __('N8N Server'),
                'breadcrumbs' => [
                    ['label' => __('Dashboard'), 'url' => route('dashboard')],
                    ['label' => __('N8N')],
                ],
            ]);
    }
}

