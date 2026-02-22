@extends('layouts.app')

@section('title', 'Journal d\'audit')
@section('header', 'Journal d\'audit')

@section('content')
<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Journal d'audit</h2>
            <p class="text-[13px] text-slate-400 mt-0.5">Historique de toutes les actions effectuées dans le système</p>
        </div>
        <a href="{{ route('audit.export', request()->only(['action', 'entity_type', 'user_id', 'date_from', 'date_to', 'source'])) }}"
           class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-slate-900 text-[13px] font-medium px-5 h-10 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Exporter Excel
        </a>
    </div>

    {{-- Filters --}}
    <div class="border-b border-slate-200 pb-4 mb-6">
        <form method="GET" action="{{ route('audit.index') }}" class="flex flex-wrap items-center gap-3">
            <select name="action" class="filter-input flex-1 min-w-0">
                <option value="">Action : Toutes</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                @endforeach
            </select>
            <select name="entity_type" class="filter-input flex-1 min-w-0">
                <option value="">Entité : Toutes</option>
                @foreach($entityTypes as $type)
                    <option value="{{ $type }}" {{ request('entity_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="filter-input">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="filter-input">
            <select name="source" class="filter-input flex-1 min-w-0">
                <option value="">Source : Toutes</option>
                <option value="web" {{ request('source') === 'web' ? 'selected' : '' }}>Web</option>
                <option value="api" {{ request('source') === 'api' ? 'selected' : '' }}>Mobile (API)</option>
            </select>
            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#2DB56B] hover:bg-[#2AAE64] text-white text-[13px] font-medium h-10 px-5 transition-colors">
                Filtrer
            </button>
            @if(request()->hasAny(['action', 'entity_type', 'date_from', 'date_to', 'source']))
                <a href="{{ route('audit.index') }}" class="text-[12px] text-slate-500 hover:text-slate-900 underline transition-colors">Réinitialiser</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="border-b border-slate-200">
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Date</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Source</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Utilisateur</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Action</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Entité</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Détails</th>
                </tr>
            </thead>
            @if($logs->count())
                @foreach($logs as $log)
                <tbody x-data="{ expanded: false }">
                    <tr class="hover:bg-slate-50/50 transition-colors border-b border-slate-100">
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="text-[13px] text-slate-900">{{ $log->created_at->format('d/m/Y') }}</div>
                            <div class="text-[11px] text-slate-400">{{ $log->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="px-5 py-3.5 border-l border-slate-100">
                            @if($log->source === 'api')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-medium rounded-full bg-blue-50 text-blue-700">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    Mobile
                                </span>
                            @elseif($log->source === 'web')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-medium rounded-full bg-slate-100 text-slate-600">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    Web
                                </span>
                            @else
                                <span class="text-[11px] text-slate-300">---</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 border-l border-slate-100">
                            <div class="text-[13px] font-medium text-slate-900">{{ $log->user->full_name ?? '---' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $log->user->role ?? '' }}</div>
                            <div class="text-[11px] text-slate-300 font-mono">{{ $log->ip_address }}</div>
                        </td>
                        <td class="px-5 py-3.5 border-l border-slate-100">
                            @php
                                $actionClass = match($log->action) {
                                    'login', 'logout' => 'text-blue-600',
                                    'create' => 'text-emerald-600',
                                    'update', 'update_financial' => 'text-amber-600',
                                    'delete' => 'text-red-600',
                                    'validate_vehicle' => 'text-emerald-600',
                                    'reject_vehicle' => 'text-red-600',
                                    default => 'text-slate-500',
                                };
                            @endphp
                            <span class="text-[13px] font-medium {{ $actionClass }}">{{ $log->action }}</span>
                        </td>
                        <td class="px-5 py-3.5 border-l border-slate-100">
                            <span class="text-[13px] font-medium text-slate-900">{{ $log->entity_type }}</span>
                            <span class="text-[11px] text-slate-400 block font-mono truncate max-w-[140px]" title="{{ $log->entity_id }}">{{ $log->entity_id }}</span>
                        </td>
                        <td class="px-5 py-3.5 border-l border-slate-100">
                            @if($log->request_body)
                                <button @click="expanded = !expanded" class="inline-flex items-center gap-1.5 text-[12px] text-slate-500 hover:text-slate-900 underline transition-colors">
                                    <svg :class="expanded && 'rotate-90'" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    <span x-text="expanded ? 'Masquer' : 'Voir les détails'"></span>
                                </button>
                            @else
                                <span class="text-[12px] text-slate-300">---</span>
                            @endif
                        </td>
                    </tr>
                    @if($log->request_body)
                    <tr x-show="expanded" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
                        <td colspan="6" class="px-5 py-4 bg-slate-50/50">
                            <div class="max-w-4xl">
                                <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Données envoyées</p>
                                <div class="bg-white p-3 overflow-x-auto">
                                    <pre class="text-[12px] text-slate-600 whitespace-pre-wrap break-words font-mono">{{ json_encode($log->request_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endif
                </tbody>
                @endforeach
            @else
                <tbody>
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-10 h-10 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <p class="text-[13px] font-medium text-slate-900">Aucune entrée trouvée</p>
                                <p class="text-[12px] text-slate-400 mt-1">Modifiez vos filtres pour voir d'autres résultats</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            @endif
        </table>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div class="px-5 py-4 border-t border-slate-200">
        {{ $logs->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
