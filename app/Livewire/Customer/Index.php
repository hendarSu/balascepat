<?php
namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Customer;
use App\Models\CustomerGroup;

class Index extends Component
{
    public $customers = [];
    public $groups = [];
    public $groupId = '';

    public function mount(): void
    {
        $this->groups = CustomerGroup::orderBy('name')->get(['id','name'])->toArray();
        $this->reload();
    }

    public function reload(): void
    {
        $q = Customer::with('group')->orderBy('name');
        if (!empty($this->groupId)) {
            $q->where('customer_group_id', $this->groupId);
        }
        $this->customers = $q->get();
    }

    public function updatedGroupId(): void
    {
        $this->reload();
    }

    public function delete(int $id): void
    {
        Customer::findOrFail($id)->delete();
        session()->flash('success', __('Customer deleted.'));
        $this->reload();
    }

    public function render()
    {
        return view('livewire.customer.index')
            ->layout('components.layouts.app', [
                'title' => __('Customers'),
                'breadcrumbs' => [
                    ['label' => __('Dashboard'), 'url' => route('dashboard')],
                    ['label' => __('Customers')],
                ],
            ]);
    }
}
