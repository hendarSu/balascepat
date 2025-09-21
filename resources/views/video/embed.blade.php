<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Embed Video</title>
        <style>
            html,body { margin:0; padding:0; height:100%; background:#000; }
            #wrap { height:100%; display:flex; }
            video { width:100%; height:100%; }
            .error { color:#fff; font-family: ui-sans-serif, system-ui, -apple-system; margin:auto; }
        </style>
    </head>
    <body>
        @isset($error)
            <div id="wrap"><div class="error">{{ $errorMessage ?? $error }}</div></div>
        @else
            <video id="video" controls playsinline></video>
        @endisset

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
    </body>
</html>

