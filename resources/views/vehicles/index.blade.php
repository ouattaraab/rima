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
                   class="filter-input block w-full pl-10 pr-4">
        </div>
    </form>

    {{-- Filters --}}
    <div>
        <form method="GET" action="{{ route('vehicles.index') }}" class="border-b border-slate-200 pb-4">
                @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif

                <div class="flex flex-wrap items-center gap-3">
                    <select name="form_status" class="filter-input flex-1 min-w-0">
                        <option value="">Fiche : Tous</option>
                        <option value="draft" @selected(request('form_status') === 'draft')>Brouillon</option>
                        <option value="synchronized" @selected(request('form_status') === 'synchronized')>Synchronisé</option>
                        <option value="validated" @selected(request('form_status') === 'validated')>Validé</option>
                        <option value="rejected" @selected(request('form_status') === 'rejected')>Rejeté</option>
                    </select>
                    <select name="vehicle_status" class="filter-input flex-1 min-w-0">
                        <option value="">Statut : Tous</option>
                        <option value="En service" @selected(request('vehicle_status') === 'En service')>En service</option>
                        <option value="En reparation" @selected(request('vehicle_status') === 'En reparation')>En réparation</option>
                        <option value="Reforme" @selected(request('vehicle_status') === 'Reforme')>Réformé</option>
                        <option value="Cede" @selected(request('vehicle_status') === 'Cede')>Cédé</option>
                    </select>
                    <select name="vehicle_type" class="filter-input flex-1 min-w-0">
                        <option value="">Type : Tous</option>
                        <option value="Auto" @selected(request('vehicle_type') === 'Auto')>Auto</option>
                        <option value="Moto" @selected(request('vehicle_type') === 'Moto')>Moto</option>
                    </select>
                    <select name="category" class="filter-input flex-1 min-w-0">
                        <option value="">Catégorie : Toutes</option>
                        <option value="Berline" @selected(request('category') === 'Berline')>Berline</option>
                        <option value="Pick-up" @selected(request('category') === 'Pick-up')>Pick-up</option>
                        <option value="Utilitaire" @selected(request('category') === 'Utilitaire')>Utilitaire</option>
                        <option value="Camion" @selected(request('category') === 'Camion')>Camion</option>
                        <option value="Moto" @selected(request('category') === 'Moto')>Moto</option>
                    </select>
                    <select name="brand" class="filter-input flex-1 min-w-0">
                        <option value="">Marque : Toutes</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand }}" @selected(request('brand') === $brand)>{{ $brand }}</option>
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
                    <div x-data="{
                        open: false,
                        selected: {{ json_encode(request('structures', [])) }},
                        search: '',
                        toggle(code) {
                            if (this.selected.includes(code)) {
                                this.selected = this.selected.filter(c => c !== code);
                            } else {
                                this.selected.push(code);
                            }
                        }
                    }" class="relative flex-1 min-w-0">
                        <input type="text" x-model="search" @focus="open = true" @click="open = true"
                               :placeholder="selected.length ? selected.length + ' structure(s) sélectionnée(s)' : 'Structure : Toutes'"
                               class="filter-input w-full" autocomplete="off">
                        <div x-show="open" @click.away="open = false; search = ''" x-transition class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-64 overflow-hidden" x-cloak>
                            <div class="overflow-y-auto max-h-52 p-1">
                                @foreach($structures as $structure)
                                <label x-show="!search || '{{ strtolower($structure->code . ' ' . ($structure->sigle ?? '') . ' ' . $structure->name) }}'.includes(search.toLowerCase())"
                                       class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 cursor-pointer text-[13px] text-slate-700">
                                    <input type="checkbox" name="structures[]" value="{{ $structure->code }}"
                                           :checked="selected.includes('{{ $structure->code }}')"
                                           @change="toggle('{{ $structure->code }}')"
                                           class="w-3.5 h-3.5 border-slate-300 text-[#2DB56B] focus:ring-0">
                                    <span>{{ $structure->code }} - {{ $structure->sigle ?? $structure->name }}</span>
                                </label>
                                @endforeach
                            </div>
                            <div x-show="selected.length > 0" class="p-2 border-t border-slate-100">
                                <button type="button" @click="selected = []; document.querySelectorAll('[name=\'structures[]\']').forEach(cb => cb.checked = false)" class="text-[11px] text-slate-500 hover:text-slate-900 underline">Tout décocher</button>
                            </div>
                        </div>
                    </div>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="filter-input">
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="filter-input">
                    <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#2DB56B] hover:bg-[#2AAE64] text-white text-[13px] font-medium h-10 px-5 transition-colors">Filtrer</button>
                    @if(request()->hasAny(['form_status', 'vehicle_status', 'vehicle_type', 'category', 'brand', 'agent', 'structures', 'date_from', 'date_to']))
                        <a href="{{ route('vehicles.index') }}" class="text-[12px] text-slate-500 hover:text-slate-900 underline transition-colors">Réinitialiser</a>
                    @endif
                </div>
        </form>
    </div>

    {{-- Table --}}
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
                                $currentSort = request('sort');
                                $currentDirection = request('direction', 'asc');
                                $baseParams = request()->except(['sort', 'direction', 'page']);
                            @endphp
                            @foreach([
                                'registration_number' => 'Immatriculation',
                                'brand' => 'Marque / Modèle',
                                'category' => 'Catégorie',
                                'vehicle_type' => 'Type',
                                'form_status' => 'Statut fiche',
                                'status' => 'Statut véhicule',
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
                                    <span class="text-[12px] text-slate-500">{{ $vehicle->category ?? '-' }}</span>
                                </td>
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

    {{-- Moto Import Modal --}}
    <div x-show="showMotoImportModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showMotoImportModal = false" x-cloak>
        <div x-show="showMotoImportModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-white border border-slate-200 w-full max-w-md mx-4 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <p class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Importer des motos</p>
            </div>
            <form action="{{ route('vehicles.motos.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="p-6 space-y-4">
                    <p class="text-[13px] text-slate-500">Selectionnez un fichier Excel (.xlsx, .xls) ou CSV contenant les colonnes :</p>
                    <p class="text-[12px] text-slate-400 mt-1"><strong>Obligatoires :</strong> immatriculation, marque, modele, n_chassis, cylindree, couleur, carburant, statut, structure_ci, type_contrat</p>
                    <p class="text-[12px] text-slate-400 mt-1"><strong>Optionnelles :</strong> immatriculation_provisoire, categorie, transmission, places, charge_utile, kilometrage, date_mise_en_circulation, date_controle_technique, equipements_speciaux, assure (oui/non), compagnie_assurance, numero_police, debut_assurance, fin_assurance, matricule_agent, direction</p>
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
