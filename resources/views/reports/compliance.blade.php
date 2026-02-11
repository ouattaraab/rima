@extends('layouts.app')

@section('title', 'Rapport de conformite')
@section('header', 'Rapport de conformite')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <p class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Conformite reglementaire</p>
        <a href="{{ route('reports.compliance.export') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white text-slate-900 text-[13px] px-4 h-10 hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Exporter Excel
        </a>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="border border-slate-200 bg-white p-5">
            <p class="text-3xl font-bold {{ $expiredInsurance->count() > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ $expiredInsurance->count() }}</p>
            <p class="mt-1 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Assurances expirees</p>
        </div>
        <div class="border border-slate-200 bg-white p-5">
            <p class="text-3xl font-bold {{ $expiredInspection->count() > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ $expiredInspection->count() }}</p>
            <p class="mt-1 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Controle technique expire</p>
        </div>
        <div class="border border-slate-200 bg-white p-5">
            <p class="text-3xl font-bold {{ $noRegistration->count() > 0 ? 'text-amber-600' : 'text-slate-900' }}">{{ $noRegistration->count() }}</p>
            <p class="mt-1 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Sans immatriculation</p>
        </div>
        <div class="border border-slate-200 bg-white p-5">
            <p class="text-3xl font-bold {{ $noInsurance->count() > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ $noInsurance->count() }}</p>
            <p class="mt-1 text-[11px] font-medium text-slate-400 uppercase tracking-wide">En service sans assurance</p>
        </div>
    </div>

    {{-- Expired insurance --}}
    @if($expiredInsurance->count())
    <div>
        <h3 class="text-[13px] font-semibold uppercase tracking-wide text-slate-900 mb-3">Assurances expirees</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Immatriculation</th>
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Marque / Modele</th>
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Compagnie</th>
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Date fin</th>
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Expire depuis</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($expiredInsurance as $v)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3.5 text-[13px] font-medium text-slate-900">
                            <a href="{{ route('vehicles.show', $v) }}" class="underline">{{ $v->registration_number ?: $v->temporary_registration ?: '-' }}</a>
                        </td>
                        <td class="px-5 py-3.5 text-[13px] text-slate-500 border-l border-slate-100">{{ $v->brand }} {{ $v->model }}</td>
                        <td class="px-5 py-3.5 text-[13px] text-slate-500 border-l border-slate-100">{{ $v->insurance_company ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-[13px] text-red-600 font-medium border-l border-slate-100">{{ $v->insurance_end_date?->format('d/m/Y') }}</td>
                        <td class="px-5 py-3.5 text-[13px] text-slate-500 border-l border-slate-100">{{ $v->insurance_end_date?->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Expired inspection --}}
    @if($expiredInspection->count())
    <div>
        <h3 class="text-[13px] font-semibold uppercase tracking-wide text-slate-900 mb-3">Controles techniques expires (> 1 an)</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Immatriculation</th>
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Marque / Modele</th>
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Date controle</th>
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Structure</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($expiredInspection as $v)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3.5 text-[13px] font-medium text-slate-900">
                            <a href="{{ route('vehicles.show', $v) }}" class="underline">{{ $v->registration_number ?: $v->temporary_registration ?: '-' }}</a>
                        </td>
                        <td class="px-5 py-3.5 text-[13px] text-slate-500 border-l border-slate-100">{{ $v->brand }} {{ $v->model }}</td>
                        <td class="px-5 py-3.5 text-[13px] text-red-600 font-medium border-l border-slate-100">{{ $v->technical_inspection_date?->format('d/m/Y') }}</td>
                        <td class="px-5 py-3.5 text-[13px] text-slate-500 border-l border-slate-100">{{ $v->structure_ci ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- No registration --}}
    @if($noRegistration->count())
    <div>
        <h3 class="text-[13px] font-semibold uppercase tracking-wide text-slate-900 mb-3">Sans immatriculation definitive</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Immat. provisoire</th>
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Marque / Modele</th>
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Type</th>
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Structure</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($noRegistration as $v)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3.5 text-[13px] font-medium text-slate-900">
                            <a href="{{ route('vehicles.show', $v) }}" class="underline">{{ $v->temporary_registration ?: '-' }}</a>
                        </td>
                        <td class="px-5 py-3.5 text-[13px] text-slate-500 border-l border-slate-100">{{ $v->brand }} {{ $v->model }}</td>
                        <td class="px-5 py-3.5 text-[13px] text-slate-500 border-l border-slate-100">{{ $v->vehicle_type }}</td>
                        <td class="px-5 py-3.5 text-[13px] text-slate-500 border-l border-slate-100">{{ $v->structure_ci ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- No insurance --}}
    @if($noInsurance->count())
    <div>
        <h3 class="text-[13px] font-semibold uppercase tracking-wide text-slate-900 mb-3">En service sans assurance</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Immatriculation</th>
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Marque / Modele</th>
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Statut</th>
                        <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Structure</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($noInsurance as $v)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3.5 text-[13px] font-medium text-slate-900">
                            <a href="{{ route('vehicles.show', $v) }}" class="underline">{{ $v->registration_number ?: $v->temporary_registration ?: '-' }}</a>
                        </td>
                        <td class="px-5 py-3.5 text-[13px] text-slate-500 border-l border-slate-100">{{ $v->brand }} {{ $v->model }}</td>
                        <td class="px-5 py-3.5 text-[13px] text-slate-500 border-l border-slate-100">{{ $v->status }}</td>
                        <td class="px-5 py-3.5 text-[13px] text-slate-500 border-l border-slate-100">{{ $v->structure_ci ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- All clear --}}
    @if($expiredInsurance->count() == 0 && $expiredInspection->count() == 0 && $noRegistration->count() == 0 && $noInsurance->count() == 0)
    <div class="border border-slate-200 bg-white px-5 py-16 text-center">
        <p class="text-[13px] text-slate-400">Aucune anomalie de conformite detectee</p>
        <p class="text-[11px] text-slate-300 mt-1">Tous les vehicules valides sont conformes</p>
    </div>
    @endif
</div>
@endsection
