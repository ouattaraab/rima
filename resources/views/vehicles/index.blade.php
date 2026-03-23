@extends('layouts.app')

@section('title', 'Véhicules')
@section('header', 'Véhicules')

@section('content')
<div class="space-y-5" x-data="{ showMotoImportModal: false }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-[13px] text-slate-500">
            <span class="font-semibold text-slate-900">{{ $vehicles->total() }}</span> véhicule(s) trouvé(s)
        </p>
        @if(!auth()->user()->isLimitedRole())
        <div class="flex items-center gap-2">
            <a href="{{ route('vehicles.export') }}?{{ request()->getQueryString() }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-[13px] font-medium text-slate-600 bg-white border border-slate-200 rounded-full hover:bg-slate-50 transition">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Exporter Excel
            </a>
            <a href="{{ route('vehicles.exportPdf') }}?{{ request()->getQueryString() }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-[13px] font-medium text-slate-600 bg-white border border-slate-200 rounded-full hover:bg-slate-50 transition">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                Exporter PDF
            </a>
            <button @click="showMotoImportModal = true" class="inline-flex items-center gap-2 px-4 py-2 text-[13px] font-medium text-slate-600 bg-white border border-slate-200 rounded-full hover:bg-slate-50 transition">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                Importer motos
            </button>
        </div>
        @endif
    </div>

    {{-- Search + Statut fiche --}}
    @php
        $defaultStatuses = ['synchronized', 'validated'];
        $selectedStatuses = request('form_status', $defaultStatuses);
        if (!is_array($selectedStatuses)) $selectedStatuses = [$selectedStatuses];
        $selectedStatuses = array_filter($selectedStatuses);
        // Si "all" est present, on ignore les statuts individuels
        $isAll = empty($selectedStatuses) || in_array('all', $selectedStatuses);
        if ($isAll) $selectedStatuses = [];
        $allStatuses = [
            'synchronized' => 'Synchronise',
            'validated' => 'Valide',
            'rejected' => 'Rejete',
            'draft' => 'Brouillon',
        ];
    @endphp
    {{-- Single unified form: search + status chips + filters + one "Filtrer" button --}}
    <div>
        <form method="GET" action="{{ route('vehicles.index') }}" class="border-b border-slate-200 pb-4">
            {{-- Search bar + Status chips --}}
            <div class="flex items-center gap-2 mb-3">
                <div class="relative flex-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par immatriculation, chassis, marque..."
                           class="filter-input block w-full" style="padding-left: 2.75rem;">
                    @if(request('search'))
                        <a href="{{ route('vehicles.index', request()->except(['search', 'page'])) }}" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>
                <div class="flex items-center gap-1.5">
                    <label class="cursor-pointer">
                        <input type="checkbox" id="cb-all" name="form_status[]" value="all" class="sr-only peer"
                               onchange="document.querySelectorAll('.status-cb').forEach(c => c.checked = false); this.form.submit()"
                               @checked($isAll)>
                        <span class="inline-block text-[12px] font-medium px-3 py-1.5 rounded-full border transition-colors peer-checked:bg-[#2DB56B] peer-checked:text-white peer-checked:border-[#2DB56B] peer-checked:hover:bg-[#2AAE64] bg-white text-slate-500 border-slate-200 hover:bg-slate-50">Tout</span>
                    </label>
                    @foreach($allStatuses as $value => $label)
                    <label class="cursor-pointer">
                        <input type="checkbox" name="form_status[]" value="{{ $value }}" class="sr-only peer status-cb"
                               onchange="document.getElementById('cb-all').checked = false; this.form.submit()"
                               @checked(in_array($value, $selectedStatuses))>
                        <span class="inline-block text-[12px] font-medium px-3 py-1.5 rounded-full border transition-colors peer-checked:bg-[#2DB56B] peer-checked:text-white peer-checked:border-[#2DB56B] peer-checked:hover:bg-[#2AAE64] bg-white text-slate-500 border-slate-200 hover:bg-slate-50">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Filters --}}
            <div class="flex flex-wrap items-center gap-3">
                <select name="vehicle_status" class="filter-input flex-1 min-w-0">
                    <option value="">Statut : Tous</option>
                    @foreach($vehicleStatuses as $vs)
                        <option value="{{ $vs->name }}" @selected(request('vehicle_status') === $vs->name)>{{ $vs->name }}</option>
                    @endforeach
                </select>
                <select name="vehicle_type" class="filter-input flex-1 min-w-0">
                    <option value="">Type : Tous</option>
                    @foreach($vehicleTypes as $vt)
                        <option value="{{ $vt->name }}" @selected(request('vehicle_type') === $vt->name)>{{ $vt->name }}</option>
                    @endforeach
                </select>
                <select name="category" class="filter-input flex-1 min-w-0">
                    <option value="">Categorie : Toutes</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->name }}" @selected(request('category') === $cat->name)>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <select name="brand" class="filter-input flex-1 min-w-0">
                    <option value="">Marque : Toutes</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->name }}" @selected(request('brand') === $brand->name)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-3 mt-3">
                <select name="agent" class="filter-input flex-1 min-w-0">
                    <option value="">Agent : Tous</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" @selected(request('agent') === $agent->id)>{{ $agent->full_name }}</option>
                    @endforeach
                </select>
                <x-structure-multiselect :structures="$structures" :selected="request('structures', [])" />
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="filter-input">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="filter-input">
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#2DB56B] hover:bg-[#2AAE64] text-white text-[13px] font-medium h-10 px-5 transition-colors">Filtrer</button>
                @if(request()->hasAny(['search', 'form_status', 'vehicle_status', 'vehicle_type', 'category', 'brand', 'agent', 'structures', 'date_from', 'date_to']))
                    <a href="{{ route('vehicles.index') }}" class="text-[12px] text-slate-500 hover:text-slate-900 underline transition-colors">Réinitialiser</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div x-data="{
        columns: {
            chassis: {{ request('search') ? 'true' : 'false' }},
            category: true,
            type: true,
            agent: true,
            date: true,
            dbcg: true,
            dfc: true,
        },
        showColMenu: false,
    }">
        {{-- Column visibility toggle --}}
        <div class="flex justify-end mb-2 relative">
            <button @click="showColMenu = !showColMenu" type="button" class="filter-input inline-flex items-center gap-1.5 text-[13px] text-slate-600 px-3 h-10">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                Colonnes
                <svg class="h-3.5 w-3.5 text-slate-400 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
            </button>
            <div x-show="showColMenu" @click.outside="showColMenu = false" x-cloak class="absolute right-0 top-11 z-10 bg-white border border-slate-200 shadow-sm py-0.5 min-w-[140px]">
                <label class="flex items-center gap-1.5 cursor-pointer text-[11px] text-slate-600 px-2.5 py-1 hover:bg-slate-50"><input type="checkbox" x-model="columns.chassis" class="border-slate-300 text-[#2DB56B] focus:ring-0 h-3 w-3"> N° Chassis</label>
                <label class="flex items-center gap-1.5 cursor-pointer text-[11px] text-slate-600 px-2.5 py-1 hover:bg-slate-50"><input type="checkbox" x-model="columns.category" class="border-slate-300 text-[#2DB56B] focus:ring-0 h-3 w-3"> Catégorie</label>
                <label class="flex items-center gap-1.5 cursor-pointer text-[11px] text-slate-600 px-2.5 py-1 hover:bg-slate-50"><input type="checkbox" x-model="columns.type" class="border-slate-300 text-[#2DB56B] focus:ring-0 h-3 w-3"> Type</label>
                <label class="flex items-center gap-1.5 cursor-pointer text-[11px] text-slate-600 px-2.5 py-1 hover:bg-slate-50"><input type="checkbox" x-model="columns.agent" class="border-slate-300 text-[#2DB56B] focus:ring-0 h-3 w-3"> Agent</label>
                <label class="flex items-center gap-1.5 cursor-pointer text-[11px] text-slate-600 px-2.5 py-1 hover:bg-slate-50"><input type="checkbox" x-model="columns.date" class="border-slate-300 text-[#2DB56B] focus:ring-0 h-3 w-3"> Date</label>
                <label class="flex items-center gap-1.5 cursor-pointer text-[11px] text-slate-600 px-2.5 py-1 hover:bg-slate-50"><input type="checkbox" x-model="columns.dbcg" class="border-slate-300 text-[#2DB56B] focus:ring-0 h-3 w-3"> DBCG</label>
                <label class="flex items-center gap-1.5 cursor-pointer text-[11px] text-slate-600 px-2.5 py-1 hover:bg-slate-50"><input type="checkbox" x-model="columns.dfc" class="border-slate-300 text-[#2DB56B] focus:ring-0 h-3 w-3"> DFC</label>
            </div>
        </div>

        @if($vehicles->isEmpty())
            <div class="flex flex-col items-center justify-center px-6 py-16">
                <p class="text-[13px] font-medium text-slate-900">Aucun véhicule trouvé</p>
                <p class="text-[12px] text-slate-400 mt-1">Essayez de modifier vos critères de recherche.</p>
            </div>
        @else
            <style>
                .sticky-col { position: sticky; z-index: 1; background: white; }
                .sticky-col-1 { left: 0; min-width: 140px; }
                .sticky-col-2 { left: 140px; min-width: 160px; }
                .sticky-col-2::after { content: ''; position: absolute; top: 0; right: 0; bottom: 0; width: 3px; background: linear-gradient(to right, rgba(0,0,0,0.06), transparent); }
                thead .sticky-col { background: #f8fafc; z-index: 2; }
                tr:hover .sticky-col { background: rgb(248 250 252 / 0.5); }
                /* Hide scrollbar but keep scroll functionality */
                .no-scrollbar { overflow-x: auto; scrollbar-width: none; -ms-overflow-style: none; }
                .no-scrollbar::-webkit-scrollbar { display: none; }
            </style>
            <div class="no-scrollbar">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50/50">
                        <tr>
                            @php
                                $currentSort = request('sort');
                                $currentDirection = request('direction', 'asc');
                                $baseParams = request()->except(['sort', 'direction', 'page']);
                                $sortIcon = function($field) use ($currentSort, $currentDirection) {
                                    if ($currentSort !== $field) return '';
                                    $path = $currentDirection === 'asc' ? 'M4.5 15.75l7.5-7.5 7.5 7.5' : 'M19.5 8.25l-7.5 7.5-7.5-7.5';
                                    return '<svg class="h-3 w-3 text-slate-900 inline ml-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="'.$path.'"/></svg>';
                                };
                                $sortHref = function($field) use ($currentSort, $currentDirection, $baseParams) {
                                    $dir = ($currentSort === $field && $currentDirection === 'asc') ? 'desc' : 'asc';
                                    return route('vehicles.index', array_merge($baseParams, ['sort' => $field, 'direction' => $dir]));
                                };
                            @endphp
                            {{-- Frozen: Immatriculation --}}
                            <th class="sticky-col sticky-col-1 px-5 py-2.5 text-left text-[11px] font-medium text-slate-400 uppercase tracking-wide">
                                <a href="{{ $sortHref('registration_number') }}" class="hover:text-slate-700 transition">Immatriculation {!! $sortIcon('registration_number') !!}</a>
                            </th>
                            {{-- Frozen: Marque / Modèle --}}
                            <th class="sticky-col sticky-col-2 px-5 py-2.5 text-left text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">
                                <a href="{{ $sortHref('brand') }}" class="hover:text-slate-700 transition">Marque / Modèle {!! $sortIcon('brand') !!}</a>
                            </th>
                            {{-- Scrollable columns --}}
                            <th x-show="columns.chassis" class="px-5 py-2.5 text-left text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">
                                <a href="{{ $sortHref('chassis_number') }}" class="hover:text-slate-700 transition">N° Chassis {!! $sortIcon('chassis_number') !!}</a>
                            </th>
                            <th x-show="columns.category" class="px-5 py-2.5 text-left text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">
                                <a href="{{ $sortHref('category') }}" class="hover:text-slate-700 transition">Catégorie {!! $sortIcon('category') !!}</a>
                            </th>
                            <th x-show="columns.type" class="px-5 py-2.5 text-left text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">
                                <a href="{{ $sortHref('vehicle_type') }}" class="hover:text-slate-700 transition">Type {!! $sortIcon('vehicle_type') !!}</a>
                            </th>
                            <th class="px-5 py-2.5 text-left text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">
                                <a href="{{ $sortHref('form_status') }}" class="hover:text-slate-700 transition">Statut fiche {!! $sortIcon('form_status') !!}</a>
                            </th>
                            <th class="px-5 py-2.5 text-left text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">
                                <a href="{{ $sortHref('status') }}" class="hover:text-slate-700 transition">Statut véhicule {!! $sortIcon('status') !!}</a>
                            </th>
                            <th x-show="columns.agent" class="px-5 py-2.5 text-left text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">
                                <a href="{{ $sortHref('collected_by') }}" class="hover:text-slate-700 transition">Agent {!! $sortIcon('collected_by') !!}</a>
                            </th>
                            <th x-show="columns.date" class="px-5 py-2.5 text-left text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">
                                <a href="{{ $sortHref('collected_at') }}" class="hover:text-slate-700 transition">Date {!! $sortIcon('collected_at') !!}</a>
                            </th>
                            <th x-show="columns.dbcg" class="px-5 py-2.5 text-center text-[11px] font-medium text-amber-500 uppercase tracking-wide border-l border-slate-200">DBCG</th>
                            <th x-show="columns.dfc" class="px-5 py-2.5 text-center text-[11px] font-medium text-blue-500 uppercase tracking-wide border-l border-slate-200">DFC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($vehicles as $vehicle)
                            <tr class="hover:bg-slate-50/50 cursor-pointer transition-colors" onclick="window.location='{{ route('vehicles.show', $vehicle) }}'">
                                {{-- Frozen: Immatriculation --}}
                                <td class="sticky-col sticky-col-1 whitespace-nowrap px-5 py-3">
                                    <span class="text-sm font-medium text-slate-900">{{ $vehicle->registration_number }}</span>
                                </td>
                                {{-- Frozen: Marque / Modèle --}}
                                <td class="sticky-col sticky-col-2 whitespace-nowrap px-5 py-3 text-sm text-slate-600 border-l border-slate-100">{{ $vehicle->brand }} {{ $vehicle->model }}</td>
                                {{-- Scrollable columns --}}
                                <td x-show="columns.chassis" class="whitespace-nowrap px-5 py-3 border-l border-slate-100">
                                    <span class="text-[12px] text-slate-400 font-mono" title="{{ $vehicle->chassis_number }}">{{ $vehicle->chassis_number ? Str::limit($vehicle->chassis_number, 12) : '-' }}</span>
                                </td>
                                <td x-show="columns.category" class="whitespace-nowrap px-5 py-3 border-l border-slate-100">
                                    <span class="text-[12px] text-slate-500">{{ $vehicle->category ?? '-' }}</span>
                                </td>
                                <td x-show="columns.type" class="whitespace-nowrap px-5 py-3 border-l border-slate-100">
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
                                    @if($vehicle->data_origin === 'import')
                                        <span class="ml-1.5 inline-flex items-center rounded-full bg-indigo-50 border border-indigo-200 px-1.5 py-0.5 text-[10px] font-medium text-indigo-600">Importé</span>
                                    @endif
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
                                <td x-show="columns.agent" class="whitespace-nowrap px-3 py-3 border-l border-slate-100">
                                    <span class="text-[12px] text-slate-500" title="{{ $vehicle->collector?->full_name }}">{{ $vehicle->collector ? Str::limit($vehicle->collector->full_name, 18) : '-' }}</span>
                                </td>
                                <td x-show="columns.date" class="whitespace-nowrap px-5 py-3 text-[12px] text-slate-400 border-l border-slate-100">{{ $vehicle->collected_at?->format('d/m/Y') ?? '-' }}</td>
                                <td x-show="columns.dbcg" class="whitespace-nowrap px-5 py-3 text-center border-l border-slate-100">
                                    @if($vehicle->is_dbcg_complete)
                                        <svg class="inline h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                    @else
                                        <svg class="inline h-4 w-4 text-slate-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
                                    @endif
                                </td>
                                <td x-show="columns.dfc" class="whitespace-nowrap px-5 py-3 text-center border-l border-slate-100">
                                    @if($vehicle->is_dfc_complete)
                                        <svg class="inline h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                    @else
                                        <svg class="inline h-4 w-4 text-slate-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
                                    @endif
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

    {{-- Moto Import Modal --}}
    <div x-show="showMotoImportModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showMotoImportModal = false" x-cloak>
        <div x-show="showMotoImportModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-white border border-slate-200 w-full max-w-md mx-4 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <p class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Importer des motos</p>
            </div>
            <form action="{{ route('vehicles.motos.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="p-6 space-y-4">
                    <p class="text-[13px] text-slate-500">Selectionnez un fichier Excel (.xlsx, .xls) ou CSV contenant les colonnes attendues.</p>
                    <a href="{{ route('vehicles.motos.template') }}" class="inline-flex items-center gap-1.5 text-[12px] font-medium text-[#2DB56B] hover:text-[#2AAE64] transition">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Telecharger le template Excel
                    </a>
                    <div>
                        <label for="moto-import-file" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Fichier</label>
                        <input type="file" name="file" id="moto-import-file" accept=".xlsx,.xls,.csv" required
                            class="w-full text-[13px] text-slate-900 file:mr-3 file:py-2 file:px-4 file:border-0 file:text-[13px] file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 file:cursor-pointer file:rounded-full">
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="showMotoImportModal = false" class="rounded-full border border-slate-200 bg-white text-slate-600 text-[13px] px-4 h-10 hover:bg-slate-50 transition-colors">Annuler</button>
                    <button type="submit" class="rounded-full bg-[#2DB56B] hover:bg-[#2AAE64] text-white text-[13px] px-4 h-10 transition-colors">Importer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
