<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main>
        @isset($breadcrumbs)
            <x-breadcrumbs :items="$breadcrumbs" />
        @endisset
        {{ $slot }}
    </flux:main>
    <x-toast />
    
</x-layouts.app.sidebar>
