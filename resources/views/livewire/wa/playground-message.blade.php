<div class="space-y-6">
    <flux:heading>{{ __('WA Unofficial — Playground Message') }}</flux:heading>

    @if(!$this->channel)
        <div class="rounded-xl border border-neutral-200 bg-white p-4 text-sm text-neutral-700 dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200">
            {{ __('Tidak ada channel WA Unofficial. Tambahkan channel dengan auth_type "wa-unofficial" terlebih dahulu.') }}
        </div>
    @else
        <div class="grid gap-4 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="mb-3 text-sm font-semibold">{{ __('Kirim Pesan Uji (POST /messages)') }}</div>
                <form wire:submit.prevent="send" class="space-y-3">
                    <flux:field>
                        <flux:label>{{ __('Client (Active)') }}</flux:label>
                        <flux:select wire:model="selectedClient">
                            <option value="">— {{ __('Pilih Client') }} —</option>
                            @foreach($clients as $c)
                                <option value="{{ $c['value'] }}">{{ $c['label'] }}</option>
                            @endforeach
                        </flux:select>
                        @error('selectedClient') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
                    </flux:field>

                    <div class="grid gap-4 md:grid-cols-2">
                        <flux:field>
                            <flux:label>{{ __('Base URL (From)') }}</flux:label>
                            <flux:input value="{{ $this->baseUrl }}" readonly />
                        </flux:field>
                        <flux:field>
                            <flux:label>clientId</flux:label>
                            <flux:input wire:model="clientId" readonly />
                            @error('clientId') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
                        </flux:field>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <flux:field>
                            <flux:label>to</flux:label>
                            <flux:input wire:model.defer="to" placeholder="628xxxxxxxxxx" />
                            @error('to') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
                        </flux:field>
                        <flux:field>
                            <flux:label>maxAttempts</flux:label>
                            <flux:input type="number" min="1" max="10" wire:model.defer="maxAttempts" />
                            @error('maxAttempts') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
                        </flux:field>
                    </div>
                    <flux:field>
                        <flux:label>text</flux:label>
                        <flux:textarea rows="3" wire:model.defer="text" placeholder="Hello from Playground" />
                        @error('text') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
                    </flux:field>

                    <div class="flex items-center gap-2">
                        <flux:button type="submit" variant="primary">{{ __('Kirim') }}</flux:button>
                    </div>
                </form>

                <div class="mt-4 space-y-2">
                    @if(session('success'))
                        <x-toast :message="session('success')" type="success" />
                    @endif
                    @if($error)
                        <div class="rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950/30 dark:text-red-300">{{ $error }}</div>
                    @endif
                    @if($response)
                        <div class="rounded border border-neutral-200 bg-neutral-50 p-3 dark:border-neutral-700 dark:bg-zinc-800">
                            <div class="mb-2 text-sm font-medium">{{ __('Response') }}</div>
                            <pre class="max-h-[360px] overflow-auto text-xs">{{ json_encode($response, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-4 text-sm dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200">
                <div class="mb-3 font-semibold">{{ __('Integrasi API') }}</div>
                <div class="space-y-2 text-xs">
                    <p>{{ __('Gunakan endpoint berikut untuk mengirim pesan:') }}</p>
                    <pre class="rounded bg-neutral-100 p-2 dark:bg-neutral-800">POST {{ $this->baseUrl }}/messages
Content-Type: application/json
x-api-key: YOUR_API_KEY</pre>
                    <p>{{ __('Dengan body (JSON):') }}</p>
                    <pre class="rounded bg-neutral-100 p-2 dark:bg-neutral-800">

{
  "clientId": "6285183013901",
  "to": "6285183013901",
  "text": "text",
  "maxAttempts": 2
}</pre>
                    <p>{{ __('Header otentikasi diambil dari konfigurasi Notification Channel (mis. x-api-key).') }}</p>
                    <p>{{ __('Respons akan berisi status pengiriman atau antrian tergantung implementasi server.') }}</p>
                </div>

                <div class="mt-5 rounded-lg border border-neutral-200 p-3 dark:border-neutral-700">
                    <div class="mb-1 font-medium">{{ __('Plugins') }} <span class="text-xs text-neutral-500">({{ __('Upcoming') }})</span></div>
                    <div class="text-xs text-neutral-600 dark:text-neutral-300">
                        {{ __('Dukungan plugin untuk memperluas kemampuan Playground (template pesan, variabel, dsb.) akan hadir berikutnya.') }}
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
