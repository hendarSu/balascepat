<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading>{{ __('Broadcast History') }}</flux:heading>
        <div class="flex items-center gap-2">
            <flux:button :href="route('broadcast.form')" icon="megaphone" wire:navigate>{{ __('New Broadcast') }}</flux:button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900">
        <table class="min-w-full text-sm">
            <thead class="bg-neutral-50 text-left text-xs uppercase text-neutral-600 dark:bg-zinc-800 dark:text-neutral-300">
                <tr>
                    <th class="px-4 py-2">{{ __('Waktu') }}</th>
                    <th class="px-4 py-2">{{ __('Nama') }}</th>
                    <th class="px-4 py-2">{{ __('Channel') }}</th>
                    <th class="px-4 py-2">{{ __('Group') }}</th>
                    <th class="px-4 py-2 text-right">{{ __('Recipients') }}</th>
                    <th class="px-4 py-2 text-right">{{ __('Aksi') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                @forelse($items as $b)
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap">{{ optional($b->sent_at)->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-2">{{ $b->name }}</td>
                        <td class="px-4 py-2">{{ $b->channel?->name }} @if($b->channel?->auth_type) ({{ $b->channel?->auth_type }}) @endif</td>
                        <td class="px-4 py-2">{{ $b->group?->name }}</td>
                        <td class="px-4 py-2 text-right">{{ $b->recipients_count }}</td>
                        <td class="px-4 py-2 text-right">
                            <flux:button size="xs" :href="route('broadcast.show', $b->id)" variant="ghost" icon="eye" wire:navigate />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-neutral-500">{{ __('Belum ada riwayat broadcast.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

