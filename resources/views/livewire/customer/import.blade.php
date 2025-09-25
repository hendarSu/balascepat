<div class="space-y-6">
    <flux:heading>{{ __('Import Customers') }}</flux:heading>

    <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
        <form wire:submit.prevent="import" class="space-y-4" enctype="multipart/form-data">
            <flux:field>
                <flux:label>{{ __('File CSV') }}</flux:label>
                <input type="file" wire:model="file" accept=".csv,text/csv" class="block w-full text-sm" />
                @error('file') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
            </flux:field>

            <flux:checkbox wire:model="createGroups">{{ __('Buat group otomatis bila belum ada') }}</flux:checkbox>

            <div class="text-xs text-neutral-600 dark:text-neutral-300">
                <div>{{ __('Header yang didukung: name, phone, email, group, notes') }}</div>
                <div>{{ __('Contoh: name,phone,email,group,notes') }}</div>
            </div>

            <div class="flex items-center gap-2">
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">{{ __('Import') }}</flux:button>
                <flux:button as="a" :href="route('customer.index')" variant="ghost" wire:navigate>{{ __('Batal') }}</flux:button>
            </div>
        </form>
    </div>
</div>

