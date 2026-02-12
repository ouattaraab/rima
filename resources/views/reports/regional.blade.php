@extends('layouts.app')

@section('title', 'Rapport regional')
@section('header', 'Rapport regional')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <p class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Avancement par region</p>
        <a href="{{ route('reports.regional.export', ['region' => $selectedRegion, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white text-slate-600 text-[13px] px-4 h-10 hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Exporter Excel
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('reports.regional') }}" class="border-b border-slate-200 pb-4">
        <div class="flex flex-wrap items-center gap-3">
            <select name="region" class="filter-input flex-1 min-w-0">
                <option value="">Region : Toutes</option>
                @foreach($regions as $r)
                    <option value="{{ $r }}" {{ $selectedRegion == $r ? 'selected' : '' }}>{{ $r }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="filter-input">
            <input type="date" name="date_to" value="{{ $dateTo }}" class="filter-input">
            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#2DB56B] hover:bg-[#2AAE64] text-white text-[13px] font-medium h-10 px-4 transition-colors">Filtrer</button>
            @if($selectedRegion || $dateFrom || $dateTo)
                <a href="{{ route('reports.regional') }}" class="text-[12px] text-slate-500 hover:text-slate-900 underline transition-colors">Reinitialiser</a>
            @endif
        </div>
    </form>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 gap-0 lg:grid-cols-4">
        <div class="p-5">
            <p class="text-3xl font-bold text-slate-900">{{ number_format($total) }}</p>
            <p class="mt-1 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Total inventories</p>
        </div>
        <div class="p-5 border-l border-dashed border-slate-200">
            <p class="text-3xl font-bold text-slate-900">{{ number_format($validated) }}</p>
            <p class="mt-1 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Valides</p>
        </div>
        <div class="p-5 border-l border-dashed border-slate-200">
            <p class="text-3xl font-bold text-emerald-600">{{ $completionRate }}%</p>
            <p class="mt-1 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Taux completude</p>
        </div>
        <div class="p-5 border-l border-dashed border-slate-200">
            <p class="text-3xl font-bold {{ $rejectionRate > 5 ? 'text-red-600' : 'text-slate-900' }}">{{ $rejectionRate }}%</p>
            <p class="mt-1 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Taux rejet</p>
        </div>
    </div>

    {{-- Bar Chart --}}
    @if($byRegion->count() > 0)
    <div class="p-6 border-t border-dashed border-slate-200 border-b">
        <h3 class="mb-4 text-[13px] font-semibold uppercase tracking-wide text-slate-900">Vehicules par structure</h3>
        <div class="relative" style="height: {{ max(300, $byRegion->count() * 28 + 80) }}px;">
            <canvas id="regionalChart"></canvas>
        </div>
    </div>
    @endif

    {{-- Table by region --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-200">
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Structure / CI</th>
                    <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Total</th>
                    <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Valides</th>
                    <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">En attente</th>
                    <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Rejetes</th>
                    <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Taux completude</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($byRegion as $row)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="text-[13px] font-medium text-slate-900">{{ $row->structure_ci }}</span>
                        @if($row->structure_name)
                            <span class="block text-[12px] text-slate-400">{{ $row->structure_name }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-[13px] text-slate-900 text-right font-mono border-l border-slate-100">{{ $row->total }}</td>
                    <td class="px-5 py-3.5 text-[13px] text-emerald-600 text-right font-mono border-l border-slate-100">{{ $row->validated }}</td>
                    <td class="px-5 py-3.5 text-[13px] text-amber-600 text-right font-mono border-l border-slate-100">{{ $row->synchronized }}</td>
                    <td class="px-5 py-3.5 text-[13px] text-red-600 text-right font-mono border-l border-slate-100">{{ $row->rejected }}</td>
                    <td class="px-5 py-3.5 text-[13px] text-right font-mono border-l border-slate-100">
                        {{ $row->total > 0 ? round(($row->validated / $row->total) * 100, 1) : 0 }}%
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <p class="text-[13px] text-slate-400">Aucune donnee disponible</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
@if($byRegion->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('regionalChart').getContext('2d');
    const labels = @json($byRegion->map(fn($r) => $r->structure_name ? $r->structure_ci . ' - ' . $r->structure_name : $r->structure_ci)->values());
    const dataValidated = @json($byRegion->pluck('validated'));
    const dataSynchronized = @json($byRegion->pluck('synchronized'));
    const dataRejected = @json($byRegion->pluck('rejected'));

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Valides',
                    data: dataValidated,
                    backgroundColor: '#2DB56B',
                    borderColor: '#2AAE64',
                    borderWidth: 0,
                    borderRadius: 2,
                },
                {
                    label: 'En attente',
                    data: dataSynchronized,
                    backgroundColor: '#f59e0b',
                    borderColor: '#d97706',
                    borderWidth: 0,
                    borderRadius: 2,
                },
                {
                    label: 'Rejetes',
                    data: dataRejected,
                    backgroundColor: '#ef4444',
                    borderColor: '#dc2626',
                    borderWidth: 0,
                    borderRadius: 2,
                },
            ],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: { size: 12, family: 'DM Sans' },
                        usePointStyle: true,
                        pointStyle: 'rect',
                        padding: 16,
                    },
                },
                tooltip: {
                    callbacks: {
                        title: function(items) {
                            return items[0].label;
                        },
                    },
                },
            },
            scales: {
                x: {
                    stacked: true,
                    grid: { color: 'rgba(226, 232, 240, 0.5)' },
                    ticks: { font: { size: 11, family: 'DM Sans' }, color: '#94a3b8' },
                },
                y: {
                    stacked: true,
                    grid: { display: false },
                    ticks: { font: { size: 12, family: 'DM Sans' }, color: '#0f172a' },
                },
            },
        },
    });
});
</script>
@endif
@endpush
