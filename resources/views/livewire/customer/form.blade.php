<div class="space-y-6">
    <flux:heading>{{ $customerId ? __('Edit Customer') : __('Tambah Customer') }}</flux:heading>

    <form wire:submit.prevent="save" class="space-y-4">
        <flux:field>
            <flux:label>{{ __('Nama') }}</flux:label>
            <flux:input wire:model.defer="name" />
            @error('name') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
        </flux:field>

        <div class="grid gap-4 md:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('Phone') }}</flux:label>
                <flux:input wire:model.defer="phone" placeholder="628xxxx" />
                @error('phone') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Email') }}</flux:label>
                <flux:input wire:model.defer="email" type="email" />
                @error('email') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
            </flux:field>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('Group') }}</flux:label>
                <flux:select wire:model="customer_group_id">
                    <option value="">—</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                    @endforeach
                </flux:select>
                @error('customer_group_id') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
            </flux:field>
        </div>

        <flux:field>
            <flux:label>{{ __('Catatan') }}</flux:label>
            <flux:textarea wire:model.defer="notes" rows="3" />
        </flux:field>

        <div class="flex items-center gap-2">
            <flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>
            <flux:button as="a" :href="route('customer.index')" variant="ghost" wire:navigate>{{ __('Batal') }}</flux:button>
        </div>
    </form>
</div>

