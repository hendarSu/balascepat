<div class="space-y-6">
    <flux:heading>{{ $channelId ? __('Edit Notification Channel') : __('Tambah Notification Channel') }}</flux:heading>
    <div
         class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4 bg-white dark:bg-zinc-900 w-full max-w-none">
        <form wire:submit.prevent="save" class="space-y-4">
            <flux:field>
                <flux:label>{{ __('Nama Channel') }}</flux:label>
                <flux:input type="text" wire:model.defer="name" required />
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Base URL') }}</flux:label>
                <flux:input type="text" wire:model.defer="base_url" required />
                @error('base_url') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </flux:field>
            {{-- WA Unofficial: use auth_type selector + required headers --}}
            @if(($auth_type ?? null) === 'wa-unofficial' || ($type ?? null) === 'wa_unoffical')
                <flux:field>
                    <flux:label>{{ __('Auth Type') }}</flux:label>
                    <select wire:model="auth_type"
                            class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-200 dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-100">
                        <option value="">{{ __('None') }}</option>
                        <option value="header">{{ __('Header') }}</option>
                        <option value="wa-unofficial">{{ __('WA Unofficial (Header Auth)') }}</option>
                    </select>
                    @error('auth_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Headers Key') }}</flux:label>
                    <flux:input type="text" wire:model.defer="headers_key" required />
                    @error('headers_key') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Headers Value') }}</flux:label>
                    <flux:input type="text" wire:model.defer="headers_value" required />
                    @error('headers_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </flux:field>
            @endif

            {{-- N8N: optional header (X-N8N-API-Key) + optional credentials --}}
            @if(($type ?? null) === 'n8n')
                <div class="mt-2 border-t border-neutral-200 dark:border-neutral-700"></div>
                <flux:subheading>{{ __('N8N Authentication (optional)') }}</flux:subheading>
                <p class="text-xs text-neutral-500 mb-2">{{ __('Anda dapat menggunakan Header API Key atau Username/Password (self-hosted).') }}</p>
                <flux:subheading>{{ __('N8N Credentials (optional)') }}</flux:subheading>
                <flux:field>
                    <flux:label>{{ __('Username / Email') }}</flux:label>
                    <flux:input type="text" wire:model.defer="n8n_username" />
                    @error('n8n_username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Password') }}</flux:label>
                    <flux:input type="password" wire:model.defer="n8n_password"
                                placeholder="{{ __('Leave blank to keep current') }}" />
                    @error('n8n_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </flux:field>
            @endif
            <div class="flex items-center gap-2 mt-4">
                <flux:button type="submit" variant="primary">{{ $channelId ? __('Update') : __('Create') }}
                </flux:button>
                <flux:link :href="route('notification-channel.index')" wire:navigate>
                    <flux:button>{{ __('Cancel') }}</flux:button>
                </flux:link>
            </div>
        </form>
    </div>
</div>
