<x-layouts.app :title="__('Video Player')" :breadcrumbs="[
    ['label' => __('Dashboard'), 'url' => route('dashboard')],
    ['label' => __('Video'), 'url' => route('video.index')],
    ['label' => __('Player')],
]">
    <div class="space-y-4">
         <flux:heading size="xl">{{ __('Videos Player') }}</flux:heading>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-3">
                <div class="aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <video id="video" class="h-full w-full" controls playsinline></video>
                </div>
            </div>
            <div>
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4 bg-white dark:bg-zinc-900">
                    <flux:heading size="sm" class="mb-2">{{ __('Your Completed Videos') }}</flux:heading>
                    <div class="space-y-1 max-h-[50vh] overflow-y-auto">
                        @forelse ($userVideos as $v)
                            <flux:link :href="route('video.player', ['playlist' => basename($v->hls_path)])" wire:navigate>{{ $v->title }}</flux:link>
                        @empty
                            <div class="text-sm text-neutral-500">{{ __('No videos yet.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('video');
            const playlist = @json($playlist);
            if (!playlist) return;
            const src = "{{ route('video.playlist', ['playlist' => '___']) }}".replace('___', playlist);
            if (Hls.isSupported()) {
                const hls = new Hls({ lowLatencyMode: true });
                hls.loadSource(src);
                hls.attachMedia(video);
            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                video.src = src;
            }
        });
    </script>
</x-layouts.app>
