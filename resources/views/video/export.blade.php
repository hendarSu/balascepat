<x-layouts.app :title="__('Video')">
    <div class="space-y-4">
        @isset($error)
            <flux:alert icon="shield-exclamation" variant="danger">{{ $errorMessage ?? $error }}</flux:alert>
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

