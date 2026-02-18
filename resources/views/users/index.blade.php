@extends('layouts.app')

@section('title', 'Utilisateurs')
@section('header', 'Utilisateurs')

@section('content')
<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Utilisateurs</h2>
            <p class="text-[13px] text-slate-400 mt-0.5">Gestion des comptes utilisateurs</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('users.export', request()->only(['search', 'role', 'organization'])) }}"
               class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-slate-900 text-[13px] font-medium px-5 h-10 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Exporter Excel
            </a>
            <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 rounded-full bg-[#2DB56B] hover:bg-[#2AAE64] text-white text-[13px] font-medium px-5 h-10 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouvel utilisateur
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="border-b border-slate-200 pb-4 mb-6">
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom, username ou email..." class="filter-input w-full">
            </div>
            <div class="sm:w-48">
                <select name="role" class="filter-input w-full">
                    <option value="">Tous les r&ocirc;les</option>
                    <option value="agent_cidec" {{ request('role') === 'agent_cidec' ? 'selected' : '' }}>Agent CIDEC</option>
                    <option value="supervisor_cidec" {{ request('role') === 'supervisor_cidec' ? 'selected' : '' }}>Superviseur CIDEC</option>
                    <option value="supervisor_sodeci" {{ request('role') === 'supervisor_sodeci' ? 'selected' : '' }}>Superviseur SODECI</option>
                    <option value="admin_sodeci" {{ request('role') === 'admin_sodeci' ? 'selected' : '' }}>Admin SODECI</option>
                </select>
            </div>
            <div class="sm:w-40">
                <select name="organization" class="filter-input w-full">
                    <option value="">Toutes les org.</option>
                    <option value="CIDEC" {{ request('organization') === 'CIDEC' ? 'selected' : '' }}>CIDEC</option>
                    <option value="SODECI" {{ request('organization') === 'SODECI' ? 'selected' : '' }}>SODECI</option>
                </select>
            </div>
            <div class="flex gap-2 items-center">
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#2DB56B] hover:bg-[#2AAE64] text-white text-[13px] font-medium h-10 px-5 transition-colors">
                    Filtrer
                </button>
                @if(request()->hasAny(['search', 'role', 'organization']))
                    <a href="{{ route('users.index') }}" class="text-[12px] text-slate-500 hover:text-slate-900 underline transition-colors">R&eacute;initialiser</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="border-b border-slate-200">
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Utilisateur</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Email</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">R&ocirc;le</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Organisation</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">R&eacute;gion</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Statut</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Derni&egrave;re connexion</th>
                    <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Actions</th>
                </tr>
            </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 bg-[#2DB56B] flex items-center justify-center shrink-0">
                                    <span class="text-white text-[10px] font-bold">{{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900">{{ $user->full_name }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $user->username }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 border-l border-slate-100">{{ $user->email }}</td>
                        <td class="px-5 py-3.5 border-l border-slate-100">
                            @switch($user->role)
                                @case('agent_cidec')
                                    <span class="text-[13px] text-slate-400">Agent CIDEC</span>
                                    @break
                                @case('supervisor_cidec')
                                    <span class="text-[13px] text-blue-600">Superviseur CIDEC</span>
                                    @break
                                @case('supervisor_sodeci')
                                    <span class="text-[13px] text-amber-600">Superviseur SODECI</span>
                                    @break
                                @case('admin_sodeci')
                                    <span class="text-[13px] text-slate-900 font-semibold">Admin SODECI</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 border-l border-slate-100">{{ $user->organization }}</td>
                        <td class="px-5 py-3.5 text-slate-500 border-l border-slate-100">{{ $user->region ?? '---' }}</td>
                        <td class="px-5 py-3.5 border-l border-slate-100">
                            @if($user->is_active)
                                <span class="text-[13px] text-emerald-600">Actif</span>
                            @else
                                <span class="text-[13px] text-slate-400">Inactif</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-slate-400 text-[12px] border-l border-slate-100">
                            {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : '---' }}
                        </td>
                        <td class="px-5 py-3.5 text-right border-l border-slate-100">
                            <a href="{{ route('users.edit', $user->id) }}" class="text-[12px] text-slate-500 hover:text-slate-900 underline transition-colors">Modifier</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-10 h-10 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <p class="text-[13px] font-medium text-slate-900">Aucun utilisateur trouv&eacute;</p>
                                <p class="text-[12px] text-slate-400 mt-1">Modifiez vos filtres ou ajoutez un utilisateur</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

        {{-- Pagination --}}
        @if($users->hasPages())
        <div class="px-5 py-4 border-t border-slate-200">
            {{ $users->withQueryString()->links() }}
        </div>
        @endif
</div>
@endsection
