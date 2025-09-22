<x-layouts.app :title="__('Dashboard')" :breadcrumbs="[[ 'label' => __('Dashboard') ]]">
    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <!-- Original Storage Card -->
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="text-sm text-neutral-500">{{ __('Original Videos') }}</div>
                <div class="mt-2 text-2xl font-semibold">{{ number_format(($storage['original'] ?? 0) / 1024 / 1024, 2) }} MB</div>
                <div class="mt-1 text-xs text-neutral-500">{{ __('Total size of uploaded source files') }}</div>
            </div>

            <!-- HLS Storage Card -->
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="text-sm text-neutral-500">{{ __('HLS Output') }}</div>
                <div class="mt-2 text-2xl font-semibold">{{ number_format(($storage['hls'] ?? 0) / 1024 / 1024, 2) }} MB</div>
                <div class="mt-1 text-xs text-neutral-500">{{ __('Segments and playlists generated') }}</div>
            </div>

            <!-- Total Storage Card -->
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="text-sm text-neutral-500">{{ __('Total Storage') }}</div>
                <div class="mt-2 text-2xl font-semibold">{{ number_format(($storage['total'] ?? 0) / 1024 / 1024, 2) }} MB</div>
                <div class="mt-1 text-xs text-neutral-500">{{ __('Original + HLS') }}</div>
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div class="text-sm font-medium text-neutral-700 dark:text-neutral-200">{{ __('Usage Overview') }}</div>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div>
                    <div class="h-2 w-full rounded bg-neutral-200 dark:bg-neutral-800">
                        @php $total = max(1, (int)($storage['total'] ?? 0)); $o = (int)($storage['original'] ?? 0); @endphp
                        <div class="h-2 rounded bg-blue-500" style="width: {{ min(100, round($o/$total*100)) }}%"></div>
                    </div>
                    <div class="mt-1 text-xs text-neutral-500">{{ __('Original share') }}</div>
                </div>
                <div>
                    <div class="h-2 w-full rounded bg-neutral-200 dark:bg-neutral-800">
                        @php $h = (int)($storage['hls'] ?? 0); @endphp
                        <div class="h-2 rounded bg-green-500" style="width: {{ min(100, round($h/$total*100)) }}%"></div>
                    </div>
                    <div class="mt-1 text-xs text-neutral-500">{{ __('HLS share') }}</div>
                </div>
                <div>
                    <div class="h-2 w-full rounded bg-neutral-200 dark:bg-neutral-800">
                        <div class="h-2 rounded bg-neutral-500" style="width: 100%"></div>
                    </div>
                    <div class="mt-1 text-xs text-neutral-500">{{ __('Total') }}</div>
                </div>
            </div>
            @if(($storage['original'] ?? 0) === 0 && ($storage['hls'] ?? 0) === 0)
                <div class="mt-4 text-xs text-neutral-500">{{ __('No storage usage yet. Upload a video to see stats.') }}</div>
            @endif
        </div>

        <!-- Traffic Chart -->
        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
            <div class="mb-3 flex items-center justify-between">
                <div class="text-sm font-medium text-neutral-700 dark:text-neutral-200">{{ __('Traffic (plays) — Last 14 days') }}</div>
            </div>
            <canvas id="trafficChart" height="70"></canvas>
        </div>

        <!-- Popular Videos -->
        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
            <div class="mb-3 flex items-center justify-between">
                <div class="text-sm font-medium text-neutral-700 dark:text-neutral-200">{{ __('Popular Videos') }}</div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-neutral-500">
                        <tr>
                            <th class="px-3 py-2">{{ __('Title') }}</th>
                            <th class="px-3 py-2">{{ __('Plays') }}</th>
                            <th class="px-3 py-2">{{ __('Status') }}</th>
                            <th class="px-3 py-2">{{ __('Created') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($popular ?? []) as $v)
                        <tr class="border-t border-neutral-200 dark:border-neutral-700">
                            <td class="px-3 py-2">{{ $v['title'] }}</td>
                            <td class="px-3 py-2">{{ number_format($v['plays']) }}</td>
                            <td class="px-3 py-2">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ match($v['status']){
                                        'completed' => 'bg-green-100 text-green-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'queued' => 'bg-sky-100 text-sky-800',
                                        'failed' => 'bg-red-100 text-red-800',
                                        default => 'bg-yellow-100 text-yellow-800'
                                    } }}
                                ">{{ ucfirst($v['status']) }}</span>
                            </td>
                            <td class="px-3 py-2">{{ \Illuminate\Support\Carbon::parse($v['created_at'])->format('Y-m-d H:i') }}</td>
                            <td class="px-3 py-2 text-right">
                                @if(($v['status'] ?? '') === 'completed' && !empty($v['hls_path']))
                                    <a class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-3 py-1.5 text-xs text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-900 dark:text-neutral-200" href="{{ route('video.player', ['playlist' => basename($v['hls_path'])]) }}" target="_blank">{{ __('Preview') }}</a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td class="px-3 py-4 text-neutral-500" colspan="5">{{ __('No data yet') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</x-layouts.app>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function(){
        const el = document.getElementById('trafficChart');
        if(!el) return;
        const data = @json($trafficDays ?? []);
        const labels = data.map(d => d.date.slice(5));
        const counts = data.map(d => d.count);
        new Chart(el, {
            type: 'line',
            data: { labels, datasets: [{ label: 'Plays', data: counts, tension: .3, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,.15)', fill: true, pointRadius: 2 }] },
            options: { responsive: true, plugins: { legend: { display: false }}, scales: { x: { grid: { display:false }}, y: { beginAtZero: true, ticks: { precision:0 } } } }
        });
    })();
</script>
@endpush
