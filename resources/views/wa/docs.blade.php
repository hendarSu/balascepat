<x-layouts.app :title="__('WA Unofficial — Docs')" :breadcrumbs="[
    ['label' => __('Dashboard'), 'url' => route('dashboard')],
    ['label' => __('WA Unofficial')],
    ['label' => __('Docs')],
]">
    @php
        $channel = \App\Models\NotificationChannel::forCurrentUser()->ofType('wa_unoffical')->first();
        $base = $channel ? rtrim((string) $channel->base_url, '/') : 'http://localhost:3100';
        $docsUrl = $base . '/docs/';
    @endphp

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">{{ __('WA Unofficial — API Docs') }}</flux:heading>
            <flux:button as="a" :href="$docsUrl" target="_blank" icon="arrow-top-right-on-square">{{ __('Open in new tab') }}</flux:button>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900 overflow-hidden">
            <iframe id="docs-iframe" src="{{ $docsUrl }}" style="width:100%; height:100%; border:0; display:block; background:white"></iframe>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const iframe = document.getElementById('docs-iframe');
            iframe.onload = function() {
                try {
                    const body = iframe.contentWindow.document.body;
                    const html = iframe.contentWindow.document.documentElement;
                    const height = Math.max(
                        body.scrollHeight, body.offsetHeight,
                        html.clientHeight, html.scrollHeight, html.offsetHeight
                    );
                    iframe.style.height = height + 'px';
                } catch (e) {
                    // Fallback for cross-origin iframes
                    iframe.style.height = '80vh';
                    console.warn('Could not dynamically set iframe height due to cross-origin restrictions. Falling back to 80vh.', e);
                }
            };
        });
    </script>
</x-layouts.app>
