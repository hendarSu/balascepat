<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading>{{ __('Customer Groups') }}</flux:heading>
        <flux:button :href="route('customer-group.create')" icon="plus" wire:navigate>{{ __('Tambah') }}</flux:button>
    </div>

    @if(session('success'))
        <x-toast :message="session('success')" type="success" />
    @endif

    @if($groups && count($groups))
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($groups as $g)
                <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-sm font-semibold">{{ $g->name }}</div>
                            <div class="mt-1 text-xs text-neutral-500 line-clamp-2">{{ $g->description }}</div>
                        </div>
                        <div class="flex items-center gap-1">
                            <flux:button size="xs" :href="route('customer-group.edit', $g->id)" icon="pencil" variant="ghost" wire:navigate />
                            <flux:button size="xs" icon="trash" variant="danger" wire:click="delete({{ $g->id }})" />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-xl border border-dashed border-neutral-300 p-8 text-center text-neutral-500 dark:border-neutral-700 dark:text-neutral-400">
            {{ __('Belum ada group.') }}
        </div>
    @endif
</div>

