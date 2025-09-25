<x-layouts.app :title="__('WA Unofficial — Session')" :breadcrumbs="[
    ['label' => __('Dashboard'), 'url' => route('dashboard')],
    ['label' => __('WA Unofficial')],
    ['label' => __('Session')],
]">
    <div class="space-y-6">
        <flux:heading>{{ __('Session') }}</flux:heading>
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4 bg-white dark:bg-zinc-900">
            <p class="text-sm text-neutral-600 dark:text-neutral-300">{{ __('Halaman ini placeholder untuk manajemen sesi WA Unofficial.') }}</p>
        </div>
    </div>
</x-layouts.app>

