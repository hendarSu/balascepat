<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main>
        @isset($breadcrumbs)
            <x-breadcrumbs :items="$breadcrumbs" />
        @endisset
        {{ $slot }}
    </flux:main>
    <x-toast />
    @php
        $bc = config('broadcasting.default');
        $pusherKey = config('broadcasting.connections.pusher.key');
    @endphp
    @if($bc === 'pusher' && $pusherKey)
        <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>
        <script>
            window.Pusher = window.Pusher || Pusher;
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: @json(config('broadcasting.connections.pusher.key')),
                cluster: @json(config('broadcasting.connections.pusher.options.cluster', 'mt1')),
                wsHost: @json(config('broadcasting.connections.pusher.options.host', request()->getHost())),
                wsPort: @json(config('broadcasting.connections.pusher.options.port', 6001)),
                wssPort: @json(config('broadcasting.connections.pusher.options.port', 6001)),
                forceTLS: @json(config('broadcasting.connections.pusher.options.useTLS', false)),
                enabledTransports: ['ws', 'wss'],
            });
        </script>
    @endif
    
</x-layouts.app.sidebar>
