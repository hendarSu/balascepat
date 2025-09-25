<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading>{{ __('Broadcast') }}</flux:heading>
    </div>

    @if (session('success'))
        <x-toast :message="session('success')" type="success" />
    @endif
    @if (session('error'))
        <x-toast :message="session('error')" type="danger" />
    @endif

    <form wire:submit.prevent="send" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('Nama Broadcast') }}</flux:label>
                <flux:input wire:model.defer="name" placeholder="Promo September" />
                @error('name') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Customer Group') }}</flux:label>
                <flux:select wire:model="group_id">
                    <option value="">— {{ __('Pilih Group') }} —</option>
                    @foreach($groups as $g)
                        <option value="{{ $g['id'] }}">{{ $g['name'] }}</option>
                    @endforeach
                </flux:select>
                @error('group_id') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
            </flux:field>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('Channel') }}</flux:label>
                <flux:select wire:model="channel_id" wire:change="loadAccountsForChannel">
                    <option value="">— {{ __('Pilih Channel') }} —</option>
                    @foreach($channels as $ch)
                        <option value="{{ $ch['id'] }}">{{ $ch['name'] }} @if($ch['auth_type']) ({{ $ch['auth_type'] }}) @endif</option>
                    @endforeach
                </flux:select>
                @error('channel_id') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
            </flux:field>

            @if($accounts && count($accounts))
                <flux:field>
                    <flux:label>{{ __('Akun (clientId)') }}</flux:label>
                    <flux:select wire:model="clientId">
                        <option value="">— {{ __('Pilih Akun') }} —</option>
                        @foreach($accounts as $a)
                            <option value="{{ $a['id'] }}">{{ $a['label'] }}</option>
                        @endforeach
                    </flux:select>
                    {{-- <div class="text-xs text-neutral-500">{{ __('Diperlukan untuk WA Unofficial') }}</div> --}}
                </flux:field>
            @endif
        </div>

        <flux:field>
            <flux:label>{{ __('Pesan') }}</flux:label>
            <flux:textarea rows="5" wire:model.defer="text" placeholder="Teks broadcast..." />
            @error('text') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
        </flux:field>

        <div class="flex items-center gap-2">
            <flux:button type="submit" variant="primary">{{ __('Kirim Broadcast') }}</flux:button>
            <flux:button type="button" variant="ghost" wire:click="loadAccountsForChannel">{{ __('Muat Akun') }}</flux:button>
        </div>
    </form>

    <div class="text-xs text-neutral-500">
        {{ __('Broadcast WA Unofficial mengirim ke endpoint /broadcasts pada base URL channel terpilih.') }}
    </div>
</div>
