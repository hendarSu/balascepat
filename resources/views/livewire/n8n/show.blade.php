<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading>{{ __('N8N Server') }}</flux:heading>
        <flux:link :href="route('notification-channel.index')" wire:navigate>
            <flux:button variant="ghost" icon="arrow-left">{{ __('Channels') }}</flux:button>
        </flux:link>
    </div>

    @if(!$channel)
        <div class="rounded-xl border border-dashed border-neutral-300 p-8 text-center text-neutral-500 dark:border-neutral-700 dark:text-neutral-400">
            {{ __('Belum ada N8N Channel. Silakan buat terlebih dahulu.') }}
            <div class="mt-4">
                <flux:link :href="route('notification-channel.create', ['type' => 'n8n'])" wire:navigate>
                    <flux:button variant="primary">{{ __('Create N8N Channel') }}</flux:button>
                </flux:link>
            </div>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="mb-3 font-medium">{{ __('Informasi Koneksi') }}</div>
                <div class="space-y-2 text-sm text-neutral-700 dark:text-neutral-200">
                    <div><span class="font-semibold">{{ __('Nama') }}:</span> {{ $channel->name }}</div>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <a href="{{ rtrim($channel->base_url, '/') }}" target="_blank" rel="noopener" class="inline-flex">
                        <flux:button variant="primary" icon="arrow-top-right-on-square">{{ __('Open N8N') }}</flux:button>
                    </a>
                    <flux:link :href="route('notification-channel.edit', $channel->id)" wire:navigate>
                        <flux:button>{{ __('Edit Channel') }}</flux:button>
                    </flux:link>
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="mb-3 font-medium">{{ __('Catatan') }}</div>
                <div class="text-sm text-neutral-600 dark:text-neutral-300">
                    {{ __('Best practice: reverse proxy N8N di domain/path yang sama, lalu embed via path tersebut. Alternatif aman: buka di tab baru.') }}
                </div>
            </div>
        </div>

        @php
            $base = trim((string) ($channel->base_url ?? ''));
            $isPath = $base !== '' && str_starts_with($base, '/');
            $isSameHost = false;
            if (!$isPath && $base !== '') {
                $host = parse_url($base, PHP_URL_HOST);
                $scheme = parse_url($base, PHP_URL_SCHEME);
                $isSameHost = $host && $host === request()->getHost() && (!$scheme || $scheme === request()->getScheme());
            }
            $embedUrl = $isPath ? url($base) : ($isSameHost ? $base : null);
        @endphp
        @if($embedUrl)
            <div class="rounded-xl border border-neutral-200 bg-white p-2 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="aspect-video w-full overflow-hidden rounded-lg bg-neutral-50 dark:bg-neutral-800">
                    <iframe src="{{ $embedUrl }}" class="h-full w-full" referrerpolicy="no-referrer"></iframe>
                </div>
                <div class="mt-2 text-xs text-neutral-500">
                    {{ __('Embedding menggunakan Base URL dari database (same-origin).') }}
                </div>
            </div>
        @else
            {{-- <div class="rounded-xl border border-amber-300 bg-amber-50 p-2 dark:border-amber-700 dark:bg-amber-900/20">
                <div class="aspect-video w-full overflow-hidden rounded-lg bg-neutral-50 dark:bg-neutral-800">
                    <iframe src="{{ route('n8n.embed') }}" class="h-full w-full" referrerpolicy="no-referrer"></iframe>
                </div>
                <div class="mt-2 text-xs text-amber-700 dark:text-amber-300">
                    {{ __('Mode proxy (fallback). Gunakan untuk pengembangan; di produksi gunakan reverse proxy same-origin.') }}
                </div>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="mb-2 font-medium">{{ __('Cara Rekomendasi Menampilkan N8N') }}</div>
                <ul class="list-disc ms-5 text-sm text-neutral-700 dark:text-neutral-200 space-y-1">
                    <li>{{ __('Simpan Base URL N8N di database sebagai path same-origin (mis. /n8n-admin) agar bisa di-embed langsung.') }}</li>
                    <li>{{ __('Atur reverse proxy Anda untuk memetakan path tersebut ke server N8N.') }}</li>
                    <li>{{ __('Izinkan embed: atur CSP frame-ancestors pada N8N/reverse proxy untuk mengizinkan origin aplikasi ini.') }}</li>
                </ul>
            </div> --}}
        @endif
    @endif
</div>
