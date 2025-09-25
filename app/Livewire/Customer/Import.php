<?php
namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use App\Models\Customer;
use App\Models\CustomerGroup;

class Import extends Component
{
    use WithFileUploads;

    public $file; // CSV file
    public $createGroups = true;

    protected function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt'],
            'createGroups' => ['boolean'],
        ];
    }

    public function import()
    {
        $this->validate();

        $path = $this->file->store('imports');
        $full = storage_path('app/'.$path);

        $handle = fopen($full, 'r');
        if (!$handle) {
            session()->flash('error', __('Tidak bisa membuka file.'));
            return;
        }

        $count = 0; $created = 0; $updated = 0; $errors = 0;
        $header = null; $map = [];
        while (($row = fgetcsv($handle)) !== false) {
            // Normalize encoding and trim
            $row = array_map(fn($v) => is_string($v) ? trim($v) : $v, $row);
            if ($header === null) {
                $header = array_map(fn($h) => Str::of((string)$h)->lower()->trim()->toString(), $row);
                foreach ($header as $i => $h) {
                    if (in_array($h, ['name','nama'])) $map['name'] = $i;
                    if (in_array($h, ['phone','telepon','hp','msisdn'])) $map['phone'] = $i;
                    if (in_array($h, ['email'])) $map['email'] = $i;
                    if (in_array($h, ['group','group_name','customer_group','kelompok'])) $map['group'] = $i;
                    if (in_array($h, ['notes','catatan'])) $map['notes'] = $i;
                }
                continue;
            }
            $count++;
            $name = isset($map['name']) ? (string)($row[$map['name']] ?? '') : '';
            $phone = isset($map['phone']) ? (string)($row[$map['phone']] ?? '') : '';
            $email = isset($map['email']) ? (string)($row[$map['email']] ?? '') : null;
            $groupName = isset($map['group']) ? (string)($row[$map['group']] ?? '') : null;
            $notes = isset($map['notes']) ? (string)($row[$map['notes']] ?? '') : null;

            if ($name === '' || $phone === '') { $errors++; continue; }

            $groupId = null;
            if ($groupName) {
                $existing = CustomerGroup::where('name', $groupName)->first();
                if (!$existing && $this->createGroups) {
                    $existing = CustomerGroup::create(['name' => $groupName]);
                }
                $groupId = $existing?->id;
            }

            $cust = Customer::where('phone', $phone)->first();
            if ($cust) {
                $cust->fill([
                    'name' => $name,
                    'email' => $email ?: null,
                    'customer_group_id' => $groupId,
                    'notes' => $notes ?: null,
                ])->save();
                $updated++;
            } else {
                Customer::create([
                    'name' => $name,
                    'phone' => $phone,
                    'email' => $email ?: null,
                    'customer_group_id' => $groupId,
                    'notes' => $notes ?: null,
                ]);
                $created++;
            }
        }
        fclose($handle);

        session()->flash('success', __('Import selesai: :count baris, :created dibuat, :updated diperbarui, :errors error.', [
            'count' => $count, 'created' => $created, 'updated' => $updated, 'errors' => $errors,
        ]));

        return redirect()->route('customer.index');
    }

    public function render()
    {
        return view('livewire.customer.import')
            ->layout('components.layouts.app', [
                'title' => __('Import Customers'),
                'breadcrumbs' => [
                    ['label' => __('Dashboard'), 'url' => route('dashboard')],
                    ['label' => __('Customers'), 'url' => route('customer.index')],
                    ['label' => __('Import')],
                ],
            ]);
    }
}

