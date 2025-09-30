<?php

return [
    // If you reverse proxy n8n under the same origin, set the path here (e.g., '/n8n-admin').
    // The UI will embed via iframe to this path.
    'reverse_path' => env('N8N_REVERSE_PATH', null),

    // Experimental: enable Laravel proxy-based embedding (not recommended for production).
    'proxy_enabled' => (bool) env('N8N_PROXY_ENABLED', false),
];

