<?php
namespace App\Livewire\Broadcast;

use Livewire\Component;
use App\Models\Broadcast;

class Index extends Component
{
    public $items = [];

    public function mount(): void
    {
        $this->items = Broadcast::with(['channel:id,name,auth_type', 'group:id,name'])
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->limit(200)
            ->get();
    }

    public function render()
    {
        return view('livewire.broadcast.index')
            ->layout('components.layouts.app', [
                'title' => __('Broadcast History'),
                'breadcrumbs' => [
                    ['label' => __('Dashboard'), 'url' => route('dashboard')],
                    ['label' => __('Broadcast History')],
                ],
            ]);
    }
}

