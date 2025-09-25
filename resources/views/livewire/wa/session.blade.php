<div class="space-y-6">
    <flux:heading>{{ __('WA Unofficial — Session') }}</flux:heading>

    @if(!$this->channel)
        <div class="rounded-xl border border-neutral-200 bg-white p-4 text-sm text-neutral-700 dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200">
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
                <div class="rounded-xl border border-dashed border-neutral-300 p-8 text-center text-neutral-500 dark:border-neutral-700 dark:text-neutral-400">
                    {{ __('Belum ada sesi.') }}
                </div>
            @else
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
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
                            </div>
                            <div class="mt-4 flex items-center gap-2">
                                <flux:button size="sm" wire:click="reconnect('{{ $phone }}')">{{ __('Reconnect') }}</flux:button>
                                <flux:button size="sm" variant="ghost" wire:click="showQr('{{ $phone }}')">{{ __('Tampilkan QR') }}</flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        (function(){
            function renderQrPlaceholders(){
                document.querySelectorAll('[data-qr-text]:not([data-qr-rendered])').forEach(function(el){
                    var text = el.getAttribute('data-qr-text');
                    if(!text) return;
                    el.setAttribute('data-qr-rendered','1');
                    try { new QRCode(el, { text: text, width: 160, height: 160 }); } catch(e) {}
                });
            }
            renderQrPlaceholders();
            document.addEventListener('livewire:navigated', renderQrPlaceholders);
            window.addEventListener('open-modal', function(e){
                // Delay slightly to allow modal DOM to mount
                setTimeout(renderQrPlaceholders, 50);
            });
        })();
        </script>

    <flux:modal name="qr-preview" :show="@entangle('qrModalOpen')" focusable class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('QR untuk :phone', ['phone' => $selectedPhone]) }}</flux:heading>
            @php $qrItem = $selectedPhone ? ($qrs[$selectedPhone] ?? null) : null; @endphp
            @if($qrItem)
                @if(is_array($qrItem) && ($qrItem['type'] ?? null) === 'text')
                    <div class="flex flex-col items-center gap-2">
                        <div data-qr-text="{{ $qrItem['data'] }}" class="mx-auto"></div>
                        <div class="text-[10px] text-neutral-500 break-all">{{ Str::limit($qrItem['data'], 120) }}</div>
                    </div>
                @else
                    @php $src = is_array($qrItem) ? ($qrItem['data'] ?? '') : (string) $qrItem; @endphp
                    <div class="text-center">
                        <img src="{{ $src }}" alt="QR" class="mx-auto h-56 w-56 object-contain" />
                    </div>
                @endif
            @else
                <div class="text-sm text-neutral-500">{{ __('QR belum tersedia.') }}</div>
            @endif

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled" wire:click="$set('qrModalOpen', false)">{{ __('Tutup') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
