<?php
namespace App\Livewire\CustomerGroup;

use Livewire\Component;
use App\Models\CustomerGroup;

class Index extends Component
{
    public $groups = [];

    public function mount(): void
    {
        $this->groups = CustomerGroup::orderBy('name')->get();
    }

    public function delete(int $id): void
    {
        $group = CustomerGroup::findOrFail($id);
        $group->delete();
        session()->flash('success', __('Group deleted.'));
        $this->groups = CustomerGroup::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.customer-group.index')
            ->layout('components.layouts.app', [
                'title' => __('Customer Groups'),
                'breadcrumbs' => [
                    ['label' => __('Dashboard'), 'url' => route('dashboard')],
                    ['label' => __('Customer Groups')],
                ],
            ]);
    }
}

