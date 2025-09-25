<div class="space-y-6">
    <flux:heading>{{ $groupId ? __('Edit Customer Group') : __('Tambah Customer Group') }}</flux:heading>

    <form wire:submit.prevent="save" class="space-y-4">
        <flux:field>
            <flux:label>{{ __('Nama') }}</flux:label>
            <flux:input wire:model.defer="name" />
            @error('name') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Deskripsi') }}</flux:label>
            <flux:textarea wire:model.defer="description" rows="3" />
        </flux:field>

        <div class="flex items-center gap-2">
            <flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>
            <flux:button as="a" :href="route('customer-group.index')" variant="ghost" wire:navigate>{{ __('Batal') }}</flux:button>
        </div>
    </form>
</div>

