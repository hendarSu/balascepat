<?php
namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Customer;
use App\Models\CustomerGroup;

class Form extends Component
{
    public $customerId;
    public $name = '';
    public $phone = '';
    public $email = '';
    public $customer_group_id = null;
    public $notes = '';

    public function mount($id = null): void
    {
        if ($id) {
            $c = Customer::findOrFail($id);
            $this->customerId = $c->id;
            $this->name = $c->name;
            $this->phone = $c->phone;
            $this->email = (string) $c->email;
            $this->customer_group_id = $c->customer_group_id;
            $this->notes = (string) $c->notes;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'customer_group_id' => ['nullable', 'exists:customer_groups,id'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function save()
    {
        $this->validate();
        Customer::updateOrCreate(
            ['id' => $this->customerId],
            [
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email ?: null,
                'customer_group_id' => $this->customer_group_id ?: null,
                'notes' => $this->notes ?: null,
            ]
        );
        return redirect()->route('customer.index');
    }

    public function render()
    {
        $groups = CustomerGroup::orderBy('name')->get(['id','name']);
        $isEdit = (bool) $this->customerId;
        return view('livewire.customer.form', compact('groups'))
            ->layout('components.layouts.app', [
                'title' => $isEdit ? __('Edit Customer') : __('Tambah Customer'),
                'breadcrumbs' => [
                    ['label' => __('Dashboard'), 'url' => route('dashboard')],
                    ['label' => __('Customers'), 'url' => route('customer.index')],
                    ['label' => $isEdit ? __('Edit') : __('Tambah')],
                ],
            ]);
    }
}

