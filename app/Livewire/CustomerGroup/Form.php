<?php
namespace App\Livewire\CustomerGroup;

use Livewire\Component;
use App\Models\CustomerGroup;

class Form extends Component
{
    public $groupId;
    public $name = '';
    public $description = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function mount($id = null): void
    {
        if ($id) {
            $g = CustomerGroup::findOrFail($id);
            $this->groupId = $g->id;
            $this->name = $g->name;
            $this->description = (string) $g->description;
        }
    }

    public function save()
    {
        $this->validate();
        CustomerGroup::updateOrCreate(
            ['id' => $this->groupId],
            ['name' => $this->name, 'description' => $this->description]
        );
        return redirect()->route('customer-group.index');
    }

    public function render()
    {
        $isEdit = (bool) $this->groupId;
        return view('livewire.customer-group.form')
            ->layout('components.layouts.app', [
                'title' => $isEdit ? __('Edit Customer Group') : __('Tambah Customer Group'),
                'breadcrumbs' => [
                    ['label' => __('Dashboard'), 'url' => route('dashboard')],
                    ['label' => __('Customer Groups'), 'url' => route('customer-group.index')],
                    ['label' => $isEdit ? __('Edit') : __('Tambah')],
                ],
            ]);
    }
}

