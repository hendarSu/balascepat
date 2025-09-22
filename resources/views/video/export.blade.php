<x-layouts.app :title="__('Video')">
    <div class="space-y-4">
        @isset($error)
            <div class="rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800 dark:border-red-700/60 dark:bg-red-900/30 dark:text-red-200">
                {{ $errorMessage ?? $error }}
            </div>
        @else
            <flux:heading>{{ $video->title }}</flux:heading>
            <div class="aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <video id="video" class="h-full w-full" controls playsinline></video>
            </div>
        @endisset
    </div>

    @empty($error)
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('video');
            const src = "{{ route('video.playlist.token', ['token' => $video->export_token]) }}";
            if (Hls.isSupported()) {
                const hls = new Hls({ lowLatencyMode: true });
                hls.loadSource(src);
                hls.attachMedia(video);
            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                video.src = src;
            }
        });
    </script>
    @endempty
</x-layouts.app>
