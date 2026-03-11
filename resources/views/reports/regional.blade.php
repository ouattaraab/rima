@extends('layouts.app')

@section('title', 'Avancement par structure')
@section('header', 'Avancement par structure')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <p class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Avancement par structure</p>
        <a href="{{ route('reports.regional.export', ['structures' => $selectedStructures ?? [], 'directions' => $selectedDirections ?? [], 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white text-slate-600 text-[13px] px-4 h-10 hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Exporter Excel
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('reports.regional') }}" class="relative z-30 border-b border-slate-200 pb-4">
        <div class="flex flex-wrap items-center gap-3">
            <x-structure-multiselect :structures="$structures" :selected="$selectedStructures ?? []" />

            {{-- Direction filter --}}
            <div x-data="{
                open: false,
                selected: {{ json_encode(is_array($selectedDirections ?? []) ? ($selectedDirections ?? []) : []) }},
                search: '',
                items: {{ $directions->map(fn($d) => ['id' => $d->id, 'l' => $d->code . ' - ' . $d->name, 's' => strtolower($d->code . ' ' . $d->name)])->values()->toJson() }},
                get filtered() {
                    if (!this.search) return this.items;
                    const q = this.search.toLowerCase();
                    return this.items.filter(d => d.s.includes(q));
                },
                toggle(id) {
                    const i = this.selected.indexOf(id);
                    if (i > -1) this.selected.splice(i, 1);
                    else this.selected.push(id);
                },
                has(id) { return this.selected.includes(id); },
                clear() { this.selected = []; },
                close() { this.open = false; this.search = ''; }
            }" class="relative flex-1 min-w-0"
               @click.away="close()"
               @keydown.escape.window="close()">
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="directions[]" :value="id">
                </template>
                <div class="relative">
                    <input type="text" x-model="search"
                           @focus="open = true" @click="open = true"
                           :placeholder="selected.length ? selected.length + ' direction(s)' : 'Direction : Toutes'"
                           class="filter-input w-full pr-8" autocomplete="off">
                    <button type="button" x-show="selected.length > 0" @click.stop="clear()"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" title="Effacer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div x-show="open" x-transition.opacity.duration.150ms
                     class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-xl overflow-hidden" x-cloak>
                    <div style="max-height: 240px; overflow-y: scroll; overscroll-behavior: contain;" x-on:wheel.stop>
                        <template x-for="item in filtered" :key="item.id">
                            <label class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-50 cursor-pointer text-[13px] text-slate-700"
                                   :class="{ 'bg-emerald-50/60': has(item.id) }">
                                <input type="checkbox" :checked="has(item.id)" @change="toggle(item.id)"
                                       class="w-3.5 h-3.5 rounded border-slate-300 text-[#2DB56B] focus:ring-0 focus:ring-offset-0 cursor-pointer shrink-0">
                                <span x-text="item.l" class="truncate"></span>
                            </label>
                        </template>
                        <div x-show="filtered.length === 0" class="px-3 py-6 text-center text-[12px] text-slate-400">Aucun resultat</div>
                    </div>
                    <div class="flex items-center justify-between px-3 py-1.5 border-t border-slate-100 bg-slate-50 text-[11px]">
                        <span class="text-slate-400">
                            <span x-text="filtered.length"></span> / <span x-text="items.length"></span>
                            <span x-show="selected.length" class="text-emerald-600 font-semibold ml-1">&bull; <span x-text="selected.length"></span> sel.</span>
                        </span>
                        <button type="button" x-show="selected.length > 0" @click.stop="clear()"
                                class="text-slate-500 hover:text-slate-900 underline">Effacer</button>
                    </div>
                </div>
            </div>

            <input type="date" name="date_from" value="{{ $dateFrom }}" class="filter-input">
            <input type="date" name="date_to" value="{{ $dateTo }}" class="filter-input">
            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#2DB56B] hover:bg-[#2AAE64] text-white text-[13px] font-medium h-10 px-4 transition-colors">Filtrer</button>
            @if(!empty($selectedStructures) || !empty($selectedDirections) || $dateFrom || $dateTo)
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
            <p class="mt-1 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Validees</p>
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

    {{-- Bar Chart by Direction --}}
    @if($byDirection->count() > 0)
    <div class="p-6 border-t border-dashed border-slate-200 border-b">
        <h3 class="mb-4 text-[13px] font-semibold uppercase tracking-wide text-slate-900">Vehicules par direction</h3>
        <div class="relative" style="height: {{ max(300, $byDirection->count() * 32 + 80) }}px;">
            <canvas id="directionChart"></canvas>
        </div>
    </div>
    @endif

    {{-- Tabs: Direction / Structure --}}
    <div x-data="{ tab: 'direction' }">
        {{-- Tab bar --}}
        <div class="flex items-center gap-0 border-b border-slate-200 px-5">
            <button @click="tab = 'direction'" type="button"
                    class="relative px-4 py-3 text-[13px] font-semibold uppercase tracking-wide transition-colors"
                    :class="tab === 'direction' ? 'text-[#2DB56B]' : 'text-slate-400 hover:text-slate-600'">
                Synthese par direction
                <span class="ml-1 text-[11px] font-normal" :class="tab === 'direction' ? 'text-[#2DB56B]' : 'text-slate-300'">({{ $byDirection->count() }})</span>
                <span class="absolute bottom-0 left-0 right-0 h-[2px] rounded-full transition-colors"
                      :class="tab === 'direction' ? 'bg-[#2DB56B]' : 'bg-transparent'"></span>
            </button>
            <button @click="tab = 'structure'" type="button"
                    class="relative px-4 py-3 text-[13px] font-semibold uppercase tracking-wide transition-colors"
                    :class="tab === 'structure' ? 'text-[#2DB56B]' : 'text-slate-400 hover:text-slate-600'">
                Detail par structure
                <span class="ml-1 text-[11px] font-normal" :class="tab === 'structure' ? 'text-[#2DB56B]' : 'text-slate-300'">({{ $byRegion->count() }})</span>
                <span class="absolute bottom-0 left-0 right-0 h-[2px] rounded-full transition-colors"
                      :class="tab === 'structure' ? 'bg-[#2DB56B]' : 'bg-transparent'"></span>
            </button>
        </div>

        {{-- Tab content: Synthese par direction --}}
        <div x-show="tab === 'direction'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Direction</th>
                        <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Total</th>
                        <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Validees</th>
                        <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">En attente</th>
                        <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Rejetees</th>
                        <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Taux completude</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($byDirection as $row)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="text-[13px] font-medium text-slate-900">{{ $row->direction_code }}</span>
                            <span class="block text-[12px] text-slate-400">{{ $row->direction_name }}</span>
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

        {{-- Tab content: Detail par structure --}}
        <div x-show="tab === 'structure'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="overflow-x-auto" x-cloak>
            @if($byRegion->count() > 0)
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Structure / CI</th>
                        <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Total</th>
                        <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Validees</th>
                        <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">En attente</th>
                        <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Rejetees</th>
                        <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Taux completude</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($byRegion as $row)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="text-[13px] font-medium text-slate-900">{{ $row->structure_ci }}-{{ $row->structure_name }}</span>
                            @if($row->structure_sigle)
                                <span class="text-[12px] text-slate-400"> ({{ $row->structure_sigle }})</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-[13px] text-slate-900 text-right font-mono border-l border-slate-100">{{ $row->total }}</td>
                        <td class="px-5 py-3.5 text-[13px] text-right font-mono border-l border-slate-100">
                            @if($row->validated > 0)
                                <a href="{{ route('vehicles.index', ['form_status' => 'validated', 'structures' => [$row->structure_ci]]) }}" class="text-emerald-600 hover:underline">{{ $row->validated }}</a>
                            @else
                                <span class="text-emerald-600">{{ $row->validated }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-[13px] text-amber-600 text-right font-mono border-l border-slate-100">{{ $row->synchronized }}</td>
                        <td class="px-5 py-3.5 text-[13px] text-right font-mono border-l border-slate-100">
                            @if($row->rejected > 0)
                                <a href="{{ route('vehicles.index', ['form_status' => 'rejected', 'structures' => [$row->structure_ci]]) }}" class="text-red-600 hover:underline">{{ $row->rejected }}</a>
                            @else
                                <span class="text-red-600">{{ $row->rejected }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-[13px] text-right font-mono border-l border-slate-100">
                            {{ $row->total > 0 ? round(($row->validated / $row->total) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="px-5 py-16 text-center">
                <p class="text-[13px] text-slate-400">Aucune donnee disponible</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($byDirection->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('directionChart').getContext('2d');
    const labels = @json($byDirection->map(fn($r) => $r->direction_code . ' - ' . $r->direction_name)->values());
    const dataValidated = @json($byDirection->pluck('validated'));
    const dataSynchronized = @json($byDirection->pluck('synchronized'));
    const dataRejected = @json($byDirection->pluck('rejected'));

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Validees',
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
                    label: 'Rejetees',
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
