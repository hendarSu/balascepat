<div class="space-y-6">
    <flux:heading>{{ __('WA Unofficial — Automation Config') }}</flux:heading>

    @if(!$this->channel)
        <div class="rounded-xl border border-neutral-200 bg-white p-4 text-sm text-neutral-700 dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200">
            {{ __('Tidak ada channel WA Unofficial. Tambahkan channel dengan auth_type "wa-unofficial" terlebih dahulu.') }}
        </div>
    @else
        <div class="space-y-4">
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="mb-3 text-sm font-semibold">{{ __('Sesi Aktif') }}</div>
                @if(empty($sessions))
                    <div class="rounded-xl border border-dashed border-neutral-300 p-8 text-center text-neutral-500 dark:border-neutral-700 dark:text-neutral-400">
                        {{ __('Belum ada sesi.') }}
                    </div>
                @else
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach($sessions as $s)
                            @php
                                $phone = is_array($s) ? ($s['clientId'] ?? $s['phone'] ?? $s['session'] ?? $s['id'] ?? '') : '';
                                $status = is_array($s) ? ($s['status'] ?? $s['state'] ?? $s['connection_state'] ?? 'unknown') : 'unknown';
                                $active = in_array(strtolower((string)$status), ['connected','open','ready','authenticated']);
                            @endphp
                            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold">{{ $phone ?: __('Unknown') }}</div>
                                        <div class="mt-1 text-xs text-neutral-500">{{ __('Status') }}: {{ $status }}</div>
                                    </div>
                                    @if($active)
                                        <span class="rounded bg-green-100 px-2 py-0.5 text-xs text-green-700 dark:bg-green-900/40 dark:text-green-300">{{ __('Active') }}</span>
                                    @endif
                                </div>
                                <div class="mt-4">
                                    <flux:button size="xs" variant="primary" wire:click="selectClient('{{ $phone }}')">{{ __('Konfigurasi') }}</flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                    <div class="mb-3 text-sm font-semibold">{{ $selectedClientId ? __('Automation N8N — Konfigurasi') : __('Buat Konfigurasi Baru') }}</div>

                    <form wire:submit.prevent="saveConfig" class="space-y-3">
                        @if(!$selectedClientId)
                            <flux:field>
                                <flux:label>{{ __('Pilih Client ID (Session)') }}</flux:label>
                                <flux:select wire:model="selectedClientId" wire:change="selectClient($event.target.value)">
                                    <option value="">— {{ __('Pilih') }} —</option>
                                    @foreach($sessions as $s)
                                        @php
                                            $cid = is_array($s) ? ($s['clientId'] ?? $s['phone'] ?? $s['session'] ?? $s['id'] ?? '') : '';
                                            $status = is_array($s) ? ($s['status'] ?? $s['state'] ?? $s['connection_state'] ?? '') : '';
                                        @endphp
                                        <option value="{{ $cid }}">{{ $cid }} @if($status) — {{ $status }} @endif</option>
                                    @endforeach
                                </flux:select>
                                @error('selectedClientId') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
                            </flux:field>
                        @else
                            <flux:field>
                                <flux:label>{{ __('Client ID') }}</flux:label>
                                <flux:input wire:model="selectedClientId" readonly />
                                @error('selectedClientId') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
                            </flux:field>
                        @endif

                        <flux:field>
                            <flux:label>{{ __('Webhook URL') }}</flux:label>
                            <flux:input wire:model.defer="webhookUrl" placeholder="https://your-n8n.tld/webhook/..." />
                            @error('webhookUrl') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Secret') }}</flux:label>
                            <flux:input wire:model.defer="secret" />
                            @error('secret') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
                        </flux:field>
                        <div class="flex items-center gap-2">
                            <flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>
                            @if($selectedClientId)
                                <flux:button type="button" variant="ghost" wire:click="fetchRemoteConfig">{{ __('Muat dari Server') }}</flux:button>
                                <flux:button type="button" variant="ghost" wire:click="resetSelection">{{ __('Reset') }}</flux:button>
                            @endif
                        </div>
                        @if(session('success'))
                            <x-toast :message="session('success')" type="success" />
                        @endif
                        @if(session('error'))
                            <x-toast :message="session('error')" type="danger" />
                        @endif
                    </form>
                </div>

                <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                    <div class="mb-3 text-sm font-semibold">{{ __('Remote Config (GET)') }}</div>
                    @if($error)
                        <div class="text-sm text-red-500">{{ $error }}</div>
                    @elseif($remoteConfig && count($remoteConfig))
                        <pre class="max-h-[420px] overflow-auto rounded bg-neutral-100 p-3 text-xs dark:bg-neutral-800">{{ json_encode($remoteConfig, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) }}</pre>
                    @else
                        <div class="text-sm text-neutral-500">{{ __('Belum ada data konfigurasi dari server.') }}</div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
