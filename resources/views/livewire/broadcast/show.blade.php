<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading>{{ __('Broadcast Detail') }}</flux:heading>
        <flux:button :href="route('broadcast.index')" variant="ghost" icon="arrow-left" wire:navigate>{{ __('Kembali') }}</flux:button>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
            <div class="mb-3 font-medium">{{ __('Informasi') }}</div>
            <div class="grid gap-2 text-sm text-neutral-700 dark:text-neutral-200">
                <div><span class="font-semibold">{{ __('Nama') }}:</span> {{ $broadcast->name }}</div>
                <div><span class="font-semibold">{{ __('Waktu') }}:</span> {{ optional($broadcast->sent_at)->format('Y-m-d H:i:s') }}</div>
                <div><span class="font-semibold">{{ __('Channel') }}:</span> {{ $broadcast->channel?->name }} @if($broadcast->channel?->auth_type) ({{ $broadcast->channel?->auth_type }}) @endif</div>
                <div><span class="font-semibold">{{ __('Group') }}:</span> {{ $broadcast->group?->name }}</div>
                <div><span class="font-semibold">{{ __('Client ID') }}:</span> {{ $broadcast->client_id ?: '—' }}</div>
                <div><span class="font-semibold">{{ __('Broadcast ID (API)') }}:</span> {{ $broadcast->external_id ?: '—' }}</div>
                <div><span class="font-semibold">{{ __('Recipients') }}:</span> {{ $broadcast->recipients_count }}</div>
            </div>
            <div class="mt-4">
                <div class="mb-1 text-sm font-medium">{{ __('Pesan') }}</div>
                <div class="rounded border border-neutral-200 bg-neutral-50 p-3 text-sm dark:border-neutral-700 dark:bg-zinc-800">{!! nl2br(e($broadcast->text)) !!}</div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="mb-3 font-medium">{{ __('Stats (WA Unofficial)') }}</div>
                @if($error)
                    <div class="text-sm text-red-500">{{ $error }}</div>
                @else
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                        <div class="rounded-lg border border-neutral-200 bg-white p-3 text-center dark:border-neutral-700 dark:bg-zinc-900">
                            <div class="text-xs text-neutral-500">{{ __('Queued') }}</div>
                            <div class="text-xl font-semibold">{{ $stats['queued'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-lg border border-neutral-200 bg-white p-3 text-center dark:border-neutral-700 dark:bg-zinc-900">
                            <div class="text-xs text-neutral-500">{{ __('Processing') }}</div>
                            <div class="text-xl font-semibold">{{ $stats['processing'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-lg border border-neutral-200 bg-white p-3 text-center dark:border-neutral-700 dark:bg-zinc-900">
                            <div class="text-xs text-neutral-500">{{ __('Sent') }}</div>
                            <div class="text-xl font-semibold">{{ $stats['sent'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-lg border border-neutral-200 bg-white p-3 text-center dark:border-neutral-700 dark:bg-zinc-900">
                            <div class="text-xs text-neutral-500">{{ __('Failed') }}</div>
                            <div class="text-xl font-semibold">{{ $stats['failed'] ?? 0 }}</div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="mb-3 font-medium">{{ __('Remote Detail (raw)') }}</div>
                @if($error)
                    <div class="text-sm text-red-500">{{ $error }}</div>
                @elseif($remote && count($remote))
                    <pre class="max-h-[420px] overflow-auto rounded bg-neutral-100 p-3 text-xs dark:bg-neutral-800">{{ json_encode($remote, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) }}</pre>
                @else
                    <div class="text-sm text-neutral-500">{{ __('Tidak ada data remote atau bukan channel WA Unofficial.') }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
        <div class="mb-2 font-medium">{{ __('Recipients (sample)') }}</div>
        <div class="text-xs text-neutral-600 dark:text-neutral-300">
            @php $list = (array) ($broadcast->recipients ?? []); @endphp
            @if(count($list))
                <div class="flex flex-wrap gap-2">
                    @foreach(array_slice($list, 0, 50) as $r)
                        <span class="rounded bg-neutral-100 px-2 py-0.5 dark:bg-neutral-800">{{ $r }}</span>
                    @endforeach
                    @if(count($list) > 50)
                        <span class="text-neutral-500">+{{ count($list) - 50 }} {{ __('lainnya') }}</span>
                    @endif
                </div>
            @else
                <div>{{ __('Tidak ada data penerima yang tersimpan.') }}</div>
            @endif
        </div>
    </div>
</div>
