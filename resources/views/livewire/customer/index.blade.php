<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <flux:heading>{{ __('Customers') }}</flux:heading>
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-2">
                <flux:select wire:model="groupId" class="min-w-[220px]">
                    <option value="">{{ __('Semua Group') }}</option>
                    @foreach($groups as $g)
                        <option value="{{ $g['id'] }}">{{ $g['name'] }}</option>
                    @endforeach
                </flux:select>
            </div>
            <flux:button :href="route('customer.import')" icon="arrow-up-tray" wire:navigate>{{ __('Import') }}</flux:button>
            <flux:button :href="route('customer.create')" icon="plus" wire:navigate>{{ __('Tambah') }}</flux:button>
        </div>
    </div>

    @if(session('success'))
        <x-toast :message="session('success')" type="success" />
    @endif

    <div class="overflow-x-auto rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900">
        <table class="min-w-full text-sm">
            <thead class="bg-neutral-50 text-left text-xs uppercase text-neutral-600 dark:bg-zinc-800 dark:text-neutral-300">
                <tr>
                    <th class="px-4 py-2">{{ __('Nama') }}</th>
                    <th class="px-4 py-2">{{ __('Phone') }}</th>
                    <th class="px-4 py-2">{{ __('Email') }}</th>
                    <th class="px-4 py-2">{{ __('Group') }}</th>
                    <th class="px-4 py-2">{{ __('Catatan') }}</th>
                    <th class="px-4 py-2 text-right">{{ __('Aksi') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                @forelse($customers as $c)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $c->name }}</td>
                        <td class="px-4 py-2">{{ $c->phone }}</td>
                        <td class="px-4 py-2">{{ $c->email }}</td>
                        <td class="px-4 py-2">{{ $c->group?->name }}</td>
                        <td class="px-4 py-2 max-w-[320px]"><div class="line-clamp-2">{{ $c->notes }}</div></td>
                        <td class="px-4 py-2 text-right whitespace-nowrap">
                            <flux:button size="xs" :href="route('customer.edit', $c->id)" variant="ghost" icon="pencil" wire:navigate />
                            <flux:button size="xs" variant="danger" icon="trash" wire:click="delete({{ $c->id }})" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-neutral-500">{{ __('Belum ada customer.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
