@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('header', 'Tableau de bord')

@section('content')
    {{-- Filters --}}
    <div class="mb-6">
        <form method="GET" action="{{ route('dashboard') }}" class="border-b border-slate-200 pb-4">
            <div class="flex flex-wrap items-center gap-3">
                <select name="region" class="filter-input flex-1 min-w-0">
                    <option value="">Région : Toutes</option>
                    @foreach($regions as $r)
                        <option value="{{ $r }}" @selected(request('region') === $r)>{{ $r }}</option>
                    @endforeach
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="filter-input">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="filter-input">
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#2DB56B] hover:bg-[#2AAE64] text-white text-[13px] font-medium h-10 px-5 transition-colors">Filtrer</button>
                @if(request()->hasAny(['region', 'date_from', 'date_to']))
                    <a href="{{ route('dashboard') }}" class="text-[12px] text-slate-500 hover:text-slate-900 underline transition-colors">Réinitialiser</a>
                @endif
            </div>
        </form>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-0 mb-8">
        <div class="p-5">
            <p class="text-3xl font-bold text-slate-900">{{ number_format($total) }}</p>
            <p class="text-[11px] text-slate-400 uppercase tracking-wide mt-1">Total véhicules</p>
        </div>
        <div class="p-5 border-l border-dashed border-slate-200">
            <p class="text-3xl font-bold text-slate-900">{{ number_format($validated) }}</p>
            <p class="text-[11px] text-slate-400 uppercase tracking-wide mt-1">Validées</p>
        </div>
        <div class="p-5 border-l border-dashed border-slate-200">
            <p class="text-3xl font-bold text-slate-900">{{ number_format($synchronized) }}</p>
            <p class="text-[11px] text-slate-400 uppercase tracking-wide mt-1">Synchronisées</p>
        </div>
        <div class="p-5 border-l border-dashed border-slate-200">
            <p class="text-3xl font-bold text-slate-900">{{ number_format($rejected) }}</p>
            <p class="text-[11px] text-slate-400 uppercase tracking-wide mt-1">Rejetées</p>
        </div>
        <div class="p-5 border-l border-dashed border-slate-200">
            <p class="text-3xl font-bold text-slate-900">{{ number_format($draft) }}</p>
            <p class="text-[11px] text-slate-400 uppercase tracking-wide mt-1">Brouillons</p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-0 mb-8 border-t border-dashed border-slate-200 border-b">
        <div class="p-6">
            <h3 class="text-[13px] font-semibold text-slate-900 uppercase tracking-wide mb-4">Répartition par type</h3>
            <div class="flex items-center justify-center" style="height: 260px;">
                <canvas id="typeChart"></canvas>
            </div>
        </div>
        <div class="p-6 lg:border-l lg:border-dashed lg:border-slate-200">
            <h3 class="text-[13px] font-semibold text-slate-900 uppercase tracking-wide mb-4">Répartition par catégorie</h3>
            <div class="flex items-center justify-center" style="height: 260px;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
        <div class="p-6 lg:border-l lg:border-dashed lg:border-slate-200">
            <h3 class="text-[13px] font-semibold text-slate-900 uppercase tracking-wide mb-4">Statut des fiches</h3>
            <div style="height: 260px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Map --}}
    <div class="p-6 mb-8 border-t border-dashed border-slate-200 border-b">
        <h3 class="text-[13px] font-semibold text-slate-900 uppercase tracking-wide mb-4">Carte des collectes</h3>
        <div id="dashboardMap" style="height: 420px; z-index: 0;"></div>
        @if(empty($mapData['features']))
            <p class="text-[12px] text-slate-400 mt-2">Aucune donnée de localisation disponible.</p>
        @endif
    </div>

    {{-- Recent Vehicles + Top Agents --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-0 border-t border-dashed border-slate-200">
        {{-- Recent Vehicles --}}
        <div class="lg:col-span-2">
            <div class="px-5 py-3.5 border-b border-dashed border-slate-200 flex items-center justify-between">
                <h3 class="text-[13px] font-semibold text-slate-900 uppercase tracking-wide">Véhicules récents</h3>
                <a href="{{ route('vehicles.index') }}" class="text-[12px] text-slate-500 hover:text-slate-900 underline transition">Voir tout</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="text-left px-5 py-2.5 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Immatriculation</th>
                            <th class="text-left px-5 py-2.5 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Marque</th>
                            <th class="text-left px-5 py-2.5 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Modèle</th>
                            <th class="text-left px-5 py-2.5 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Statut</th>
                            <th class="text-left px-5 py-2.5 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Agent</th>
                            <th class="text-left px-5 py-2.5 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($recentVehicles as $vehicle)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-5 py-2.5">
                                <a href="{{ route('vehicles.show', $vehicle) }}" class="text-sm font-medium text-slate-900 underline decoration-slate-300 hover:decoration-slate-900">{{ $vehicle->registration_number }}</a>
                            </td>
                            <td class="px-5 py-2.5 text-slate-600 border-l border-slate-100">{{ $vehicle->brand }}</td>
                            <td class="px-5 py-2.5 text-slate-600 border-l border-slate-100">{{ $vehicle->model }}</td>
                            <td class="px-5 py-2.5 border-l border-slate-100">
                                @php
                                    $statusClass = match($vehicle->form_status) {
                                        'validated' => 'text-emerald-600',
                                        'synchronized' => 'text-amber-600',
                                        'rejected' => 'text-red-600',
                                        default => 'text-slate-400',
                                    };
                                    $statusLabel = match($vehicle->form_status) {
                                        'validated' => 'Validé',
                                        'synchronized' => 'Synchronisé',
                                        'rejected' => 'Rejeté',
                                        'draft' => 'Brouillon',
                                        default => $vehicle->form_status,
                                    };
                                @endphp
                                <span class="text-[12px] font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-5 py-2.5 text-slate-500 border-l border-slate-100">{{ $vehicle->collector->full_name ?? '-' }}</td>
                            <td class="px-5 py-2.5 text-slate-400 text-[12px] border-l border-slate-100">{{ $vehicle->collected_at ? $vehicle->collected_at->format('d/m/Y') : '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-[13px] text-slate-400">Aucun véhicule enregistré.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Agents --}}
        <div class="lg:border-l lg:border-dashed lg:border-slate-200">
            <div class="px-5 py-3.5 border-b border-dashed border-slate-200">
                <h3 class="text-[13px] font-semibold text-slate-900 uppercase tracking-wide">Top agents</h3>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($topAgents as $index => $agent)
                <div class="flex items-center gap-3 px-5 py-3">
                    <span class="text-[12px] font-bold text-slate-300 w-5 text-right shrink-0">{{ $index + 1 }}</span>
                    <div class="w-7 h-7 bg-[#2DB56B] flex items-center justify-center shrink-0">
                        <span class="text-white text-[10px] font-bold">{{ $agent->initials }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-medium text-slate-900 truncate">{{ $agent->full_name }}</p>
                    </div>
                    <span class="text-[12px] font-semibold text-slate-500">{{ $agent->vehicles_count }}</span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-[13px] text-slate-400">Aucun agent enregistré.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    #dashboardMap { border: 1px solid #e2e8f0; }
    .leaflet-popup-content-wrapper { border-radius: 0; box-shadow: 0 1px 4px rgba(0,0,0,.15); }
    .leaflet-popup-tip { display: none; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chartColors = ['#0f172a', '#2DB56B', '#f59e0b', '#6366f1', '#ec4899', '#94a3b8'];

        const byTypeData = @json($byType);
        const typeLabels = Object.keys(byTypeData);
        const typeValues = Object.values(byTypeData);

        if (typeLabels.length > 0) {
            new Chart(document.getElementById('typeChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: typeLabels,
                    datasets: [{ data: typeValues, backgroundColor: chartColors.slice(0, typeLabels.length), borderWidth: 0, hoverOffset: 4 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '65%',
                    plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyleWidth: 8, font: { size: 11, family: 'DM Sans' } } } }
                }
            });
        }

        const byCategoryData = @json($byCategory);
        const categoryLabels = Object.keys(byCategoryData);
        const categoryValues = Object.values(byCategoryData);

        if (categoryLabels.length > 0) {
            new Chart(document.getElementById('categoryChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: categoryLabels,
                    datasets: [{ data: categoryValues, backgroundColor: chartColors.slice(0, categoryLabels.length), borderWidth: 0, hoverOffset: 4 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '65%',
                    plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyleWidth: 8, font: { size: 11, family: 'DM Sans' } } } }
                }
            });
        }

        const byStatusData = @json($byStatus);
        const statusLabels = Object.keys(byStatusData);
        const statusValues = Object.values(byStatusData);
        const statusLabelMap = { 'validated': 'Validé', 'synchronized': 'Synchronisé', 'rejected': 'Rejeté', 'draft': 'Brouillon' };
        const statusColorMap = { 'validated': '#2DB56B', 'synchronized': '#f59e0b', 'rejected': '#ef4444', 'draft': '#94a3b8' };
        const statusBarColors = statusLabels.map(l => statusColorMap[l.toLowerCase()] || '#94a3b8');
        const statusLabelsFr = statusLabels.map(l => statusLabelMap[l.toLowerCase()] || l);

        if (statusLabels.length > 0) {
            new Chart(document.getElementById('statusChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: statusLabelsFr,
                    datasets: [{ label: 'Fiches', data: statusValues, backgroundColor: statusBarColors, borderRadius: 2, borderSkipped: false, barPercentage: 0.5 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0, font: { size: 11, family: 'DM Sans' } }, grid: { color: '#f1f5f9' } },
                        x: { ticks: { font: { size: 11, family: 'DM Sans' } }, grid: { display: false } }
                    }
                }
            });
        }
    });
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var map = L.map('dashboardMap').setView([7.54, -5.55], 7);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        var geojsonData = @json($mapData);

        if (geojsonData.features && geojsonData.features.length > 0) {
            var statusColors = {
                'validated': '#059669',
                'synchronized': '#d97706',
                'rejected': '#dc2626',
                'draft': '#94a3b8'
            };

            var bounds = [];

            geojsonData.features.forEach(function (feature) {
                var coords = feature.geometry.coordinates;
                var props = feature.properties;
                var color = statusColors[props.form_status] || '#0f172a';
                var latLng = [coords[1], coords[0]];

                bounds.push(latLng);

                var marker = L.circleMarker(latLng, {
                    radius: 6,
                    fillColor: color,
                    color: '#fff',
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.85
                }).addTo(map);

                var mapStatusLabels = { 'validated': 'Validé', 'synchronized': 'Synchronisé', 'rejected': 'Rejeté', 'draft': 'Brouillon' };
                var popupContent = '<div style="font-family: DM Sans, sans-serif; font-size: 11px; line-height: 1.4; font-weight: 400;">'
                    + '<span style="font-weight: 500;">' + (props.registration_number || '-') + '</span><br>'
                    + '<span style="color: #64748b;">' + ((props.brand || '') + ' ' + (props.model || '')).trim() + '</span><br>'
                    + '<span style="color: ' + color + '; font-weight: 500; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;">' + (mapStatusLabels[props.form_status] || props.form_status || '-') + '</span>'
                    + '</div>';

                marker.bindPopup(popupContent);
            });

            // Auto-fit map to show all markers
            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [30, 30], maxZoom: 14 });
            }
        }
    });
</script>
@endpush
