<x-layouts.app :title="__('WA Unofficial — Plugins (Upcoming)')" :breadcrumbs="[
    ['label' => __('Dashboard'), 'url' => route('dashboard')],
    ['label' => __('WA Unofficial')],
    ['label' => __('Plugins (Upcoming)')],
]">
    <div class="space-y-4">
        <flux:heading size="xl">{{ __('Plugins (Upcoming)') }}</flux:heading>
        <div class="rounded-xl border border-neutral-200 bg-white p-4 text-sm text-neutral-700 dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200">
            {{ __('Dukungan plugin untuk memperluas integrasi (template pesan, variabel, transformasi payload, dsb.) akan hadir berikutnya.') }}
        </div>
    </div>
</x-layouts.app>

