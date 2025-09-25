<x-layouts.app :title="__('Dashboard')" :breadcrumbs="[['label' => __('Dashboard')]]">
    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <h1>{{ __('Welcome, :name', ['name' => auth()->user()->name]) }}</h1>
        </div>
</x-layouts.app>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush
