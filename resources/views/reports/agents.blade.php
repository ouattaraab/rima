@extends('layouts.app')

@section('title', 'Statistiques par agent')
@section('header', 'Statistiques par agent')

@section('content')
<div class="space-y-5">

    {{-- KPI Cards — dashboard style --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-0 mb-2">
        <div class="p-5">
            <p class="text-3xl font-bold text-slate-900">{{ $agents->count() }}</p>
            <p class="text-[11px] text-slate-400 uppercase tracking-wide mt-1">Agents actifs</p>
        </div>
        <div class="p-5 border-l border-dashed border-slate-200">
            <p class="text-3xl font-bold text-slate-900">{{ number_format($agents->sum('total')) }}</p>
            <p class="text-[11px] text-slate-400 uppercase tracking-wide mt-1">Total collectes</p>
        </div>
        <div class="p-5 border-l border-dashed border-slate-200">
            <p class="text-3xl font-bold text-slate-900">{{ number_format($agents->sum('synchronized')) }}</p>
            <p class="text-[11px] text-slate-400 uppercase tracking-wide mt-1">Synchronisées</p>
        </div>
        <div class="p-5 border-l border-dashed border-slate-200">
            <p class="text-3xl font-bold text-emerald-600">{{ number_format($agents->sum('validated')) }}</p>
            <p class="text-[11px] text-slate-400 uppercase tracking-wide mt-1">Validées</p>
        </div>
        <div class="p-5 border-l border-dashed border-slate-200">
            <p class="text-3xl font-bold text-rose-600">{{ number_format($agents->sum('rejected')) }}</p>
            <p class="text-[11px] text-slate-400 uppercase tracking-wide mt-1">Rejetées</p>
        </div>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('reports.agents') }}">
        @if(request('date_from'))<input type="hidden" name="date_from" value="{{ request('date_from') }}">@endif
        @if(request('date_to'))<input type="hidden" name="date_to" value="{{ request('date_to') }}">@endif
        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom ou identifiant..."
                       class="filter-input block w-full" style="padding-left: 2.75rem;">
            </div>
        </div>
    </form>

    {{-- Filters --}}
    <div>
        <form method="GET" action="{{ route('reports.agents') }}" class="border-b border-slate-200 pb-4">
            @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
            <div class="flex flex-wrap items-center gap-3">
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="filter-input" placeholder="Date début">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="filter-input" placeholder="Date fin">
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#2DB56B] hover:bg-[#2AAE64] text-white text-[13px] font-medium h-10 px-5 transition-colors">Filtrer</button>
                @if(request()->hasAny(['search', 'date_from', 'date_to']))
                    <a href="{{ route('reports.agents') }}" class="text-[12px] text-slate-500 hover:text-slate-900 underline transition-colors">Réinitialiser</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Header count --}}
    <div class="flex items-center justify-between">
        <p class="text-[13px] text-slate-500">
            <span class="font-semibold text-slate-900">{{ $agents->count() }}</span> agent(s) trouvé(s)
        </p>
    </div>

    {{-- Table --}}
    <div>
        @if($agents->isEmpty())
            <div class="flex flex-col items-center justify-center px-6 py-16">
                <p class="text-[13px] font-medium text-slate-900">Aucun agent CIDEC trouvé</p>
                <p class="text-[12px] text-slate-400 mt-1">Essayez de modifier vos critères de recherche.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr>
                            @php
                                $currentSort = request('sort', 'total');
                                $currentDirection = request('direction', 'desc');
                                $baseParams = request()->except(['sort', 'direction']);
                            @endphp
                            @foreach([
                                'full_name' => 'Agent',
                                'region' => 'Région',
                                'total' => 'Collectes',
                                'synchronized' => 'Sync.',
                                'validated' => 'Validées',
                                'rejected' => 'Rejetées',
                                'rejection_rate' => 'Taux rejet',
                                'last_collection' => 'Dernière collecte',
                            ] as $sortField => $label)
                                @php
                                    $newDirection = ($currentSort === $sortField && $currentDirection === 'asc') ? 'desc' : 'asc';
                                    if ($currentSort !== $sortField) $newDirection = in_array($sortField, ['full_name', 'region', 'last_collection']) ? 'asc' : 'desc';
                                    $sortUrl = route('reports.agents', array_merge($baseParams, ['sort' => $sortField, 'direction' => $newDirection]));
                                    $isNumeric = !in_array($sortField, ['full_name', 'region', 'last_collection']);
                                @endphp
                                <th class="px-5 py-2.5 text-{{ $isNumeric ? 'right' : 'left' }} text-[11px] font-medium text-slate-400 uppercase tracking-wide {{ !$loop->first ? 'border-l border-slate-200' : '' }}">
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
                        @foreach($agents as $agent)
                        <tr class="hover:bg-slate-50/50">
                            <td class="whitespace-nowrap px-5 py-3">
                                <p class="text-sm font-medium text-slate-900">{{ $agent->full_name }}</p>
                                <p class="text-[11px] text-slate-400">{{ $agent->username }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-[12px] text-slate-500 border-l border-slate-100">{{ $agent->region ?? '-' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-sm font-semibold text-slate-900 text-right border-l border-slate-100">{{ $agent->total }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-sm text-amber-600 text-right border-l border-slate-100">{{ $agent->synchronized }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-sm text-emerald-600 text-right border-l border-slate-100">{{ $agent->validated }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-sm text-rose-600 text-right border-l border-slate-100">{{ $agent->rejected }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right border-l border-slate-100">
                                @php
                                    $rate = $agent->rejection_rate;
                                    $rateColor = $rate < 10 ? 'text-emerald-600' : ($rate <= 25 ? 'text-amber-600' : 'text-rose-600');
                                @endphp
                                <span class="text-[12px] font-semibold {{ $rateColor }}">{{ $rate }}%</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-[12px] text-slate-400 border-l border-slate-100">{{ $agent->last_collection ?? '-' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right border-l border-slate-100">
                                <a href="{{ route('reports.agents.show', $agent->id) }}" class="text-[12px] font-medium text-slate-500 hover:text-slate-900 underline transition">Détails</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
