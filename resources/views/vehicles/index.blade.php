@extends('layouts.app')

@section('title', 'Vehicules')
@section('header', 'Vehicules')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-[13px] text-slate-500">
            <span class="font-semibold text-slate-900">{{ $vehicles->total() }}</span> vehicule(s) trouve(s)
        </p>
        <a href="{{ route('vehicles.export') }}?{{ request()->getQueryString() }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-[13px] font-medium text-slate-600 bg-white border border-slate-200 rounded-full hover:bg-slate-50 transition">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Exporter Excel
        </a>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('vehicles.index') }}">
        @foreach(request()->except(['search', 'page']) as $key => $value)
            @if($value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
        @endforeach
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par immatriculation, chassis, marque..."
                   class="block w-full h-10 pl-10 pr-4 bg-white border border-slate-200 text-sm text-slate-900 placeholder-slate-300/70 focus:outline-none focus:border-slate-900 focus:ring-0 transition">
        </div>
    </form>

    {{-- Filters --}}
    <div>
        <form method="GET" action="{{ route('vehicles.index') }}" class="border-b border-slate-200 pb-4">
                @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif

                <div class="flex flex-wrap items-center gap-3">
                    <select name="form_status" class="flex-1 min-w-0 h-10 px-3 bg-white border border-slate-200 text-[13px] text-slate-700 focus:outline-none focus:border-slate-900 focus:ring-0 appearance-none">
                        <option value="">Fiche : Tous</option>
                        <option value="draft" @selected(request('form_status') === 'draft')>Brouillon</option>
                        <option value="synchronized" @selected(request('form_status') === 'synchronized')>Synchronise</option>
                        <option value="validated" @selected(request('form_status') === 'validated')>Valide</option>
                        <option value="rejected" @selected(request('form_status') === 'rejected')>Rejete</option>
                    </select>
                    <select name="vehicle_status" class="flex-1 min-w-0 h-10 px-3 bg-white border border-slate-200 text-[13px] text-slate-700 focus:outline-none focus:border-slate-900 focus:ring-0 appearance-none">
                        <option value="">Statut : Tous</option>
                        <option value="En service" @selected(request('vehicle_status') === 'En service')>En service</option>
                        <option value="En reparation" @selected(request('vehicle_status') === 'En reparation')>En reparation</option>
                        <option value="Reforme" @selected(request('vehicle_status') === 'Reforme')>Reforme</option>
                        <option value="Cede" @selected(request('vehicle_status') === 'Cede')>Cede</option>
                    </select>
                    <select name="vehicle_type" class="flex-1 min-w-0 h-10 px-3 bg-white border border-slate-200 text-[13px] text-slate-700 focus:outline-none focus:border-slate-900 focus:ring-0 appearance-none">
                        <option value="">Type : Tous</option>
                        <option value="Auto" @selected(request('vehicle_type') === 'Auto')>Auto</option>
                        <option value="Moto" @selected(request('vehicle_type') === 'Moto')>Moto</option>
                    </select>
                    <select name="brand" class="flex-1 min-w-0 h-10 px-3 bg-white border border-slate-200 text-[13px] text-slate-700 focus:outline-none focus:border-slate-900 focus:ring-0 appearance-none">
                        <option value="">Marque : Toutes</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand }}" @selected(request('brand') === $brand)>{{ $brand }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-wrap items-center gap-3 mt-3">
                    <select name="agent" class="flex-1 min-w-0 h-10 px-3 bg-white border border-slate-200 text-[13px] text-slate-700 focus:outline-none focus:border-slate-900 focus:ring-0 appearance-none">
                        <option value="">Agent : Tous</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" @selected(request('agent') === $agent->id)>{{ $agent->full_name }}</option>
                        @endforeach
                    </select>
                    <select name="region" class="flex-1 min-w-0 h-10 px-3 bg-white border border-slate-200 text-[13px] text-slate-700 focus:outline-none focus:border-slate-900 focus:ring-0 appearance-none">
                        <option value="">Region : Toutes</option>
                        @foreach($regions as $r)
                            <option value="{{ $r }}" @selected(request('region') === $r)>{{ $r }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="h-10 px-3 bg-white border border-slate-200 text-[13px] text-slate-700 focus:outline-none focus:border-slate-900 focus:ring-0">
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="h-10 px-3 bg-white border border-slate-200 text-[13px] text-slate-700 focus:outline-none focus:border-slate-900 focus:ring-0">
                    <button type="submit" class="inline-flex items-center justify-center rounded-full bg-slate-900 hover:bg-black text-white text-[13px] font-medium h-10 px-4 transition-colors">Filtrer</button>
                    @if(request()->hasAny(['form_status', 'vehicle_status', 'vehicle_type', 'brand', 'agent', 'region', 'date_from', 'date_to']))
                        <a href="{{ route('vehicles.index') }}" class="text-[12px] text-slate-500 hover:text-slate-900 underline transition-colors">Reinitialiser</a>
                    @endif
                </div>
        </form>
    </div>

    {{-- Table --}}
    <div>
        @if($vehicles->isEmpty())
            <div class="flex flex-col items-center justify-center px-6 py-16">
                <p class="text-[13px] font-medium text-slate-900">Aucun vehicule trouve</p>
                <p class="text-[12px] text-slate-400 mt-1">Essayez de modifier vos criteres de recherche.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr>
                            @php
                                $currentSort = request('sort');
                                $currentDirection = request('direction', 'asc');
                                $baseParams = request()->except(['sort', 'direction', 'page']);
                            @endphp
                            @foreach([
                                'registration_number' => 'Immatriculation',
                                'brand' => 'Marque / Modele',
                                'vehicle_type' => 'Type',
                                'form_status' => 'Statut fiche',
                                'status' => 'Statut vehicule',
                                'collected_by' => 'Agent',
                                'collected_at' => 'Date',
                            ] as $sortField => $label)
                                @php
                                    $newDirection = ($currentSort === $sortField && $currentDirection === 'asc') ? 'desc' : 'asc';
                                    $sortUrl = route('vehicles.index', array_merge($baseParams, ['sort' => $sortField, 'direction' => $newDirection]));
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
                                    @endphp
                                    <span class="text-[12px] font-medium {{ $fClass }}">{{ ucfirst($vehicle->form_status) }}</span>
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
                                <td class="whitespace-nowrap px-5 py-3 text-sm text-slate-500 border-l border-slate-100">{{ $vehicle->collector?->full_name ?? '-' }}</td>
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
