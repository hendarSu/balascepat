<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Platform')" class="grid">
                <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                                   wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>

                <flux:navlist.item icon="megaphone" :href="route('broadcast.form')"
                                   :current="request()->routeIs('broadcast.form')" wire:navigate>
                    {{ __('Broadcast') }}
                </flux:navlist.item>
                <flux:navlist.item icon="clock" :href="route('broadcast.index')"
                                   :current="request()->routeIs('broadcast.index') || request()->routeIs('broadcast.show')"
                                   wire:navigate>
                    {{ __('Broadcast History') }}
                </flux:navlist.item>
                {{-- <flux:navlist.item icon="film" :href="route('video.index')"
                                   :current="request()->routeIs('video.*')" wire:navigate>{{ __('Videos') }}
                </flux:navlist.item> --}}
                <flux:navlist.group :heading="__('Customers')" class="grid mt-2">
                    <flux:navlist.item icon="users" :href="route('customer.index')"
                                       :current="request()->routeIs('customer.*')" wire:navigate>
                        {{ __('Customers') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="folder" :href="route('customer-group.index')"
                                       :current="request()->routeIs('customer-group.*')" wire:navigate>
                        {{ __('Customer Groups') }}
                    </flux:navlist.item>
                </flux:navlist.group>
                <flux:navlist.group :heading="__('Channels')" class="grid mt-2">
                    <flux:navlist.item icon="plus" :href="route('notification-channel.create', ['type' => 'n8n'])"
                                       :current="request()->fullUrlIs(route('notification-channel.create', ['type' => 'n8n']))"
                                       wire:navigate>
                        {{ __('Create N8N Channel') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="chat-bubble-bottom-center-text" :href="route('notification-channel.index')"
                                       :current="request()->routeIs('notification-channel.*')" wire:navigate>
                        {{ __('Notification Setting') }}
                    </flux:navlist.item>
                </flux:navlist.group>
                @php
                    // Show WA Unofficial menu only if channel type is 'wa_unofficial'
                    // Keep backward compatibility for legacy typo 'wa_unoffical'
                    $hasWaUnofficial = \App\Models\NotificationChannel::forCurrentUser()
                        ->whereIn('type', ['wa_unofficial', 'wa_unoffical'])
                        ->exists();
                @endphp
                @if($hasWaUnofficial)
                    <flux:navlist variant="outline" class="mt-2">
                        <flux:navlist.group :heading="__('WA Unofficial')" class="grid">
                            <flux:navlist.item icon="chat-bubble-left-right" :href="route('wa.session')"
                                               :current="request()->routeIs('wa.session')" wire:navigate>{{ __('Session') }}
                            </flux:navlist.item>
                            <flux:navlist.item icon="bolt" :href="route('wa.automation')"
                                               :current="request()->routeIs('wa.automation')" wire:navigate>
                                {{ __('Automation Config') }}
                            </flux:navlist.item>
                            <flux:navlist.item icon="beaker" :href="route('wa.playground.message')"
                                               :current="request()->routeIs('wa.playground.message')" wire:navigate>
                                {{ __('Playground Message') }}
                            </flux:navlist.item>
                            <flux:navlist.item icon="puzzle-piece" :href="route('wa.plugins')"
                                               :current="request()->routeIs('wa.plugins')" wire:navigate>
                                {{ __('Plugins') }}
                            </flux:navlist.item>
                            <flux:navlist.item icon="book-open" :href="route('wa.docs')"
                                               :current="request()->routeIs('wa.docs')">
                                {{ __('Docs') }}
                            </flux:navlist.item>
                        </flux:navlist.group>
                    </flux:navlist>
                @endif
            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

        <flux:navlist variant="outline">
            {{-- <flux:navlist.item icon="chat-bubble-bottom-center-text" :href="route('notification-channel.index')"
                               :current="request()->routeIs('notification-channel.*')" wire:navigate>
                {{ __('Notification Setting') }}
            </flux:navlist.item> --}}
        </flux:navlist>

        <!-- Desktop User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                          icon:trailing="chevrons-up-down" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                      class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full"
                                    data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                      class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full"
                                    data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>
