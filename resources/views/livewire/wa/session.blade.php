<div class="space-y-6">
    <flux:heading>{{ __('WA Unofficial — Session') }}</flux:heading>

    @if(!$this->channel)
        <div
             class="rounded-xl border border-neutral-200 bg-white p-4 text-sm text-neutral-700 dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200">
            {{ __('Tidak ada channel WA Unofficial. Tambahkan channel dengan auth_type "wa-unofficial" terlebih dahulu.') }}
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="mb-3 text-sm font-semibold">{{ __('Buat Sesi Baru') }}</div>
                <form wire:submit.prevent="createSession" class="space-y-3">
                    <flux:field>
                        <flux:label>{{ __('Nomor (msisdn)') }}</flux:label>
                        <flux:input wire:model.defer="phone" placeholder="628xxxx" />
                        @error('phone') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
                    </flux:field>
                    <div class="flex items-center gap-2">
                        <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
                        <flux:button type="button" wire:click="refreshSessions">{{ __('Refresh List') }}</flux:button>
                    </div>
                </form>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="mb-3 text-sm font-semibold">{{ __('Informasi Channel') }}</div>
                <div class="text-xs text-neutral-600 dark:text-neutral-300">
                    <div><span class="font-medium">{{ __('Name') }}:</span> {{ $this->channel->name }}</div>
                    <div class="truncate"><span class="font-medium">{{ __('Base URL') }}:</span> {{ $this->baseUrl }}</div>
                    <div><span class="font-medium">{{ __('Header') }}:</span> {{ $this->channel->headers_key ?: '—' }}</div>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <flux:subheading>{{ __('Daftar Sesi') }}</flux:subheading>
            @if(empty($sessions))
                <div
                     class="rounded-xl border border-dashed border-neutral-300 p-8 text-center text-neutral-500 dark:border-neutral-700 dark:text-neutral-400">
                    {{ __('Belum ada sesi.') }}
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($sessions as $s)
                        @php
                            $phone = is_array($s) ? ($s['clientId'] ?? $s['phone'] ?? $s['session'] ?? $s['id'] ?? '') : '';
                            $status = is_array($s) ? ($s['status'] ?? $s['state'] ?? $s['connection_state'] ?? 'unknown') : 'unknown';
                        @endphp
                        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold">{{ $phone ?: __('Unknown') }}</div>
                                    <div class="mt-1 text-xs text-neutral-500">{{ __('Status') }}: {{ $status }}</div>
                                </div>
                                <div class="flex items-center">
                                    <flux:button size="xs" variant="danger" icon="trash" class="shrink-0" wire:click="openDelete('{{ $phone }}')">
                                    </flux:button>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center gap-2">
                                <flux:button size="sm" wire:click="reconnect('{{ $phone }}')">{{ __('Reconnect') }}</flux:button>
                                <flux:button size="sm" variant="ghost" wire:click="showQr('{{ $phone }}')">{{ __('Tampilkan QR') }}
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <flux:modal name="qr-preview" wire:model.self="qrModalOpen" x-on:close="$wire.set('qrModalOpen', false)" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('QR untuk :phone', ['phone' => $selectedPhone]) }}</flux:heading>
            @php $qrItem = $selectedPhone ? ($qrs[$selectedPhone] ?? null) : null; @endphp
            @if($qrItem)
                @php $src = is_array($qrItem) ? ($qrItem['data'] ?? '') : (string) $qrItem; @endphp
                <div class="text-center">
                    <img src="{{ $src }}" alt="QR" class="mx-auto h-56 w-56 object-contain" />
                </div>
                {{-- <div class="flex justify-center">
                    <a href="{{ $src }}" download="qr-{{ $selectedPhone }}.png">
                        <flux:button size="sm" icon="arrow-down-tray">{{ __('Download QR') }}</flux:button>
                    </a>
                </div> --}}
            @else
                <div class="text-sm text-neutral-500">{{ __('QR belum tersedia.') }}</div>
            @endif

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled" wire:click="$set('qrModalOpen', false)">{{ __('Tutup') }}
                    </flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="confirm-delete-wa" wire:model.self="deleteModalOpen" x-on:close="$wire.set('deleteModalOpen', false)" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Hapus Akun WA') }}</flux:heading>
            <flux:subheading>
                {{ __('Anda yakin ingin menghapus akun :phone? Tindakan ini tidak dapat dibatalkan.', ['phone' => $selectedPhone]) }}
            </flux:subheading>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="deleteAccount">{{ __('Hapus') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
