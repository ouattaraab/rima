@extends('layouts.app')

@section('title', $agent->full_name . ' — Agent')
@section('header', 'Détail agent')

@section('content')
<div class="space-y-5">

    {{-- Back link + Agent header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('reports.agents') }}" class="inline-flex items-center gap-1 text-[12px] text-slate-500 hover:text-slate-900 transition">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Retour aux agents
        </a>
    </div>

    <div class="flex items-center gap-4">
        <div class="w-11 h-11 bg-[#2DB56B] flex items-center justify-center shrink-0">
            <span class="text-white text-sm font-bold">{{ $agent->initials }}</span>
        </div>
        <div>
            <h2 class="text-lg font-bold text-slate-900">{{ $agent->full_name }}</h2>
            <p class="text-[12px] text-slate-400">{{ $agent->username }}@if($agent->region) · {{ $agent->region }}@endif</p>
        </div>
    </div>

    {{-- KPI Cards — dashboard style --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-0">
        <div class="p-5">
            <p class="text-3xl font-bold text-slate-900">{{ number_format($total) }}</p>
            <p class="text-[11px] text-slate-400 uppercase tracking-wide mt-1">Total collectes</p>
        </div>
        <div class="p-5 border-l border-dashed border-slate-200">
            <p class="text-3xl font-bold text-slate-900">{{ number_format($synchronized) }}</p>
            <p class="text-[11px] text-slate-400 uppercase tracking-wide mt-1">Synchronisées</p>
        </div>
        <div class="p-5 border-l border-dashed border-slate-200">
            <p class="text-3xl font-bold text-emerald-600">{{ number_format($validated) }}</p>
            <p class="text-[11px] text-slate-400 uppercase tracking-wide mt-1">Validées</p>
        </div>
        <div class="p-5 border-l border-dashed border-slate-200">
            <p class="text-3xl font-bold text-rose-600">{{ number_format($rejected) }}</p>
            <p class="text-[11px] text-slate-400 uppercase tracking-wide mt-1">Rejetées</p>
        </div>
        <div class="p-5 border-l border-dashed border-slate-200">
            @php
                $rateColor = $rejectionRate < 10 ? 'text-emerald-600' : ($rejectionRate <= 25 ? 'text-amber-600' : 'text-rose-600');
            @endphp
            <p class="text-3xl font-bold {{ $rateColor }}">{{ $rejectionRate }}%</p>
            <p class="text-[11px] text-slate-400 uppercase tracking-wide mt-1">Taux de rejet</p>
        </div>
    </div>

    {{-- Map --}}
    <div class="border-t border-dashed border-slate-200 border-b p-6">
        <h3 class="text-[13px] font-semibold text-slate-900 uppercase tracking-wide mb-4">Carte des collectes</h3>
        <div id="agentMap" style="height: 360px; z-index: 0;"></div>
        @if(empty($mapData['features']))
            <p class="text-[12px] text-slate-400 mt-2">Aucune donnée de localisation disponible pour cet agent.</p>
        @endif
    </div>

    {{-- Search + Status filter --}}
    <form method="GET" action="{{ route('reports.agents.show', $agent->id) }}">
        @if($dateFrom)<input type="hidden" name="date_from" value="{{ $dateFrom }}">@endif
        @if($dateTo)<input type="hidden" name="date_to" value="{{ $dateTo }}">@endif
        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par immatriculation, chassis, marque..."
                       class="filter-input block w-full" style="padding-left: 2.75rem;">
            </div>
            <select name="form_status" onchange="this.form.submit()" class="filter-input w-auto min-w-[200px]">
                <option value="">Tous les statuts</option>
                <option value="synchronized" @selected(request('form_status') === 'synchronized')>Synchronisé</option>
                <option value="validated" @selected(request('form_status') === 'validated')>Validé</option>
                <option value="rejected" @selected(request('form_status') === 'rejected')>Rejeté</option>
                <option value="draft" @selected(request('form_status') === 'draft')>Brouillon</option>
            </select>
        </div>
    </form>

    {{-- Date filters --}}
    <div>
        <form method="GET" action="{{ route('reports.agents.show', $agent->id) }}" class="border-b border-slate-200 pb-4">
            @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
            @if(request('form_status'))<input type="hidden" name="form_status" value="{{ request('form_status') }}">@endif
            <div class="flex flex-wrap items-center gap-3">
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="filter-input">
                <input type="date" name="date_to" value="{{ $dateTo }}" class="filter-input">
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#2DB56B] hover:bg-[#2AAE64] text-white text-[13px] font-medium h-10 px-5 transition-colors">Filtrer</button>
                @if(request()->hasAny(['search', 'form_status', 'date_from', 'date_to']))
                    <a href="{{ route('reports.agents.show', $agent->id) }}" class="text-[12px] text-slate-500 hover:text-slate-900 underline transition-colors">Réinitialiser</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Count --}}
    <div class="flex items-center justify-between">
        <p class="text-[13px] text-slate-500">
            <span class="font-semibold text-slate-900">{{ $vehicles->total() }}</span> véhicule(s) trouvé(s)
        </p>
    </div>

    {{-- Vehicles table --}}
    <div>
        @if($vehicles->isEmpty())
            <div class="flex flex-col items-center justify-center px-6 py-16">
                <p class="text-[13px] font-medium text-slate-900">Aucun véhicule trouvé</p>
                <p class="text-[12px] text-slate-400 mt-1">Essayez de modifier vos critères de recherche.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr>
                            @php
                                $currentSort = request('sort', 'collected_at');
                                $currentDirection = request('direction', 'desc');
                                $baseParams = request()->except(['sort', 'direction', 'page']);
                            @endphp
                            @foreach([
                                'registration_number' => 'Immatriculation',
                                'brand' => 'Marque / Modèle',
                                'vehicle_type' => 'Type',
                                'form_status' => 'Statut fiche',
                                'status' => 'Statut véhicule',
                                'collected_at' => 'Date',
                            ] as $sortField => $label)
                                @php
                                    $newDirection = ($currentSort === $sortField && $currentDirection === 'asc') ? 'desc' : 'asc';
                                    $sortUrl = route('reports.agents.show', array_merge(['user' => $agent->id], $baseParams, ['sort' => $sortField, 'direction' => $newDirection]));
                                @endphp
                                <th class="px-5 py-2.5 text-left text-[11px] font-medium text-slate-400 uppercase tracking-wide {{ !$loop->first ? 'border-l border-slate-200' : '' }}">
                                    <a href="{{ $sortUrl }}" class="inline-flex items-center gap-1 hover:text-slate-700 transition">
                                        {{ $label }}
                                        @if($currentSort === $sortField)
                                            <svg class="h-3 w-3 text-slate-900" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                @if($currentDirection === 'asc')
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                                @endif
                                            </svg>
                                        @endif
                                    </a>
                                </th>
                            @endforeach
                            <th class="px-5 py-2.5 text-right text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($vehicles as $vehicle)
                            <tr class="hover:bg-slate-50/50">
                                <td class="whitespace-nowrap px-5 py-3">
                                    <a href="{{ route('vehicles.show', $vehicle) }}" class="text-sm font-medium text-slate-900 underline decoration-slate-300 hover:decoration-slate-900">{{ $vehicle->registration_number }}</a>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-sm text-slate-600 border-l border-slate-100">{{ $vehicle->brand }} {{ $vehicle->model }}</td>
                                <td class="whitespace-nowrap px-5 py-3 border-l border-slate-100">
                                    <span class="text-[12px] text-slate-500">{{ $vehicle->vehicle_type }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 border-l border-slate-100">
                                    @php
                                        $fClass = match($vehicle->form_status) {
                                            'validated' => 'text-emerald-600',
                                            'synchronized' => 'text-amber-600',
                                            'rejected' => 'text-red-600',
                                            default => 'text-slate-400',
                                        };
                                        $fLabel = match($vehicle->form_status) {
                                            'validated' => 'Validé',
                                            'synchronized' => 'Synchronisé',
                                            'rejected' => 'Rejeté',
                                            'draft' => 'Brouillon',
                                            default => $vehicle->form_status,
                                        };
                                    @endphp
                                    <span class="text-[12px] font-medium {{ $fClass }}">{{ $fLabel }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 border-l border-slate-100">
                                    @php
                                        $vClass = match($vehicle->status) {
                                            'En service' => 'text-emerald-600',
                                            'En reparation' => 'text-amber-600',
                                            'Reforme' => 'text-slate-400',
                                            'Cede' => 'text-red-600',
                                            default => 'text-slate-400',
                                        };
                                    @endphp
                                    <span class="text-[12px] font-medium {{ $vClass }}">{{ $vehicle->status ?? '-' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-[12px] text-slate-400 border-l border-slate-100">{{ $vehicle->collected_at?->format('d/m/Y') ?? '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right border-l border-slate-100">
                                    <a href="{{ route('vehicles.show', $vehicle) }}" class="text-[12px] font-medium text-slate-500 hover:text-slate-900 underline transition">Voir</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-5 py-3">
                {{ $vehicles->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    #agentMap { border: 1px solid #e2e8f0; }
    .leaflet-popup-content-wrapper { border-radius: 0; box-shadow: 0 1px 4px rgba(0,0,0,.15); }
    .leaflet-popup-tip { display: none; }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var map = L.map('agentMap').setView([7.54, -5.55], 7);

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

                var statusLabels = { 'validated': 'Validé', 'synchronized': 'Synchronisé', 'rejected': 'Rejeté', 'draft': 'Brouillon' };
                var popupContent = '<div style="font-family: DM Sans, sans-serif; font-size: 11px; line-height: 1.4; font-weight: 400;">'
                    + '<span style="font-weight: 500;">' + (props.registration_number || '-') + '</span><br>'
                    + '<span style="color: #64748b;">' + ((props.brand || '') + ' ' + (props.model || '')).trim() + '</span><br>'
                    + '<span style="color: ' + color + '; font-weight: 500; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;">' + (statusLabels[props.form_status] || props.form_status || '-') + '</span>'
                    + '</div>';

                marker.bindPopup(popupContent);
            });

            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [30, 30], maxZoom: 14 });
            }
        }
    });
</script>
@endpush
