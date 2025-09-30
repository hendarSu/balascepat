<div class="space-y-8">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">{{ __('Notification Channels') }}</flux:heading>
            {{-- <flux:link :href="route('notification-channel.create')" wire:navigate>
                <flux:button variant="primary">
                    <span class="inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('Tambah Channel') }}
                    </span>
                </flux:button>
            </flux:link> --}}
        </div>

        <div class="space-y-4">
            <flux:subheading>{{ __('Terpasang') }}</flux:subheading>
            @if(count($channels))
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($channels as $channel)
                        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">{{ $channel->name }}</div>
                                    <div class="mt-1 text-xs text-neutral-500 dark:text-neutral-400 truncate">{{ $channel->base_url }}</div>
                                </div>
                                <div>
                                    @if($channel->auth_type)
                                        <span class="rounded-md bg-neutral-100 px-2 py-0.5 text-[10px] uppercase tracking-wide text-neutral-700 dark:bg-zinc-800 dark:text-neutral-200">{{ $channel->auth_type }}</span>
                                    @else
                                        <span class="text-neutral-400 text-xs">{{ __('None') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-4 flex items-center gap-2">
                                <flux:link :href="route('notification-channel.edit', $channel->id)" wire:navigate>
                                    <flux:button size="sm">{{ __('Edit') }}</flux:button>
                                </flux:link>
                                <flux:button size="sm" variant="danger" wire:click="delete({{ $channel->id }})" onclick="if(!confirm('{{ __('Hapus channel ini?') }}')) return false;">{{ __('Hapus') }}</flux:button>
                                @if($channel->type === 'n8n')
                                    <flux:link :href="route('n8n.show')" wire:navigate>
                                        <flux:button size="sm" variant="ghost">{{ __('Detail') }}</flux:button>
                                    </flux:link>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-xl border border-dashed border-neutral-300 p-8 text-center text-neutral-500 dark:border-neutral-700 dark:text-neutral-400">
                    {{ __('Belum ada channel terpasang.') }}
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <flux:subheading>{{ __('Tambah Channel Baru') }}</flux:subheading>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                    <div class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">{{ __('WA Unofficial') }}</div>
                    <div class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">{{ __('Integrasi WhatsApp Unofficial dengan autentikasi via Header.') }}</div>
                    <div class="mt-4">
                        <flux:link :href="route('notification-channel.create', ['type' => 'wa-unofficial'])" wire:navigate>
                            <flux:button size="sm" variant="primary">{{ __('Integration') }}</flux:button>
                        </flux:link>
                    </div>
                </div>
                <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                    <div class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">{{ __('N8N') }}</div>
                    <div class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">{{ __('Simpan akses ke N8N server.') }}</div>
                    <div class="mt-4">
                        <flux:link :href="route('notification-channel.create', ['type' => 'n8n'])" wire:navigate>
                            <flux:button size="sm" variant="primary">{{ __('Integration') }}</flux:button>
                        </flux:link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
