@extends('layouts.app')

@section('title', 'Donn&eacute;es financi&egrave;res')
@section('header', 'Donn&eacute;es financi&egrave;res')

@section('content')
<div class="mx-auto max-w-4xl"
     x-data="{
         mode: '{{ old('financing_mode', $vehicle->financing_mode ?? 'Direct') }}',
         errors: {},
         validate(e) {
             this.errors = {};
             if (this.mode === 'Leasing') {
                 if (!(this.$refs.bank_name?.value || '').trim()) this.errors.bank_name = 'Le nom de la banque est obligatoire en leasing.';
                 if (!(this.$refs.contract_number?.value || '').trim()) this.errors.contract_number = 'Le numéro de contrat est obligatoire en leasing.';
             }
             if (Object.keys(this.errors).length) { e.preventDefault(); return; }
         }
     }">

    {{-- Back link --}}
    <a href="{{ route('vehicles.show', $vehicle) }}" class="inline-flex items-center gap-1.5 text-[12px] text-slate-500 underline hover:text-slate-900 transition mb-6">
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Retour &agrave; la fiche
    </a>

    <form method="POST" action="{{ route('vehicles.updateFinancial', $vehicle->id) }}" novalidate
          @submit="validate($event)">
        @csrf
        @method('PUT')

        {{-- Section: Mode de financement --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-x-12 gap-y-5 py-8 border-b border-slate-200">
            <div>
                <p class="text-[13px] font-semibold text-slate-900">Mode de financement</p>
                <p class="text-[12px] text-slate-400 mt-1 leading-relaxed">D&eacute;finit comment le v&eacute;hicule a &eacute;t&eacute; acquis par la SODECI.</p>
            </div>
            <div class="lg:col-span-2">
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="financing_mode" value="Leasing" x-model="mode"
                               class="w-4 h-4 border-slate-300 text-slate-900 focus:ring-0 focus:ring-offset-0">
                        <span class="text-[13px] text-slate-900" :class="mode === 'Leasing' && 'font-medium'">Leasing</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="financing_mode" value="Direct" x-model="mode"
                               class="w-4 h-4 border-slate-300 text-slate-900 focus:ring-0 focus:ring-offset-0">
                        <span class="text-[13px] text-slate-900" :class="mode === 'Direct' && 'font-medium'">Achat direct</span>
                    </label>
                </div>
                @error('financing_mode')
                    <p class="text-[12px] text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Section: Informations leasing --}}
        <div x-show="mode === 'Leasing'" x-collapse x-cloak>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-x-12 gap-y-5 py-8 border-b border-slate-200">
                <div>
                    <p class="text-[13px] font-semibold text-slate-900">Informations leasing</p>
                    <p class="text-[12px] text-slate-400 mt-1 leading-relaxed">Banque, num&eacute;ro de contrat et p&eacute;riode de pr&eacute;l&egrave;vement du leasing.</p>
                </div>
                <div class="lg:col-span-2 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="bank_name" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Banque <span class="text-red-500">*</span></label>
                            <input type="text" name="bank_name" id="bank_name" x-ref="bank_name" value="{{ old('bank_name', $vehicle->bank_name) }}" placeholder="Nom de la banque"
                                   class="w-full h-10 px-3 border focus:outline-none focus:ring-0 text-[13px] text-slate-900 placeholder-slate-300 transition"
                                   :class="errors.bank_name ? 'border-red-400 focus:border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'"
                                   @input="delete errors.bank_name">
                            <p x-show="errors.bank_name" x-text="errors.bank_name" class="mt-1 text-[12px] text-red-500"></p>
                            @error('bank_name')<p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="contract_number" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Num&eacute;ro de contrat <span class="text-red-500">*</span></label>
                            <input type="text" name="contract_number" id="contract_number" x-ref="contract_number" value="{{ old('contract_number', $vehicle->contract_number) }}" placeholder="Num&eacute;ro du contrat"
                                   class="w-full h-10 px-3 border focus:outline-none focus:ring-0 text-[13px] text-slate-900 placeholder-slate-300 transition"
                                   :class="errors.contract_number ? 'border-red-400 focus:border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'"
                                   @input="delete errors.contract_number">
                            <p x-show="errors.contract_number" x-text="errors.contract_number" class="mt-1 text-[12px] text-red-500"></p>
                            @error('contract_number')<p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="withdrawal_start_date" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Date d&eacute;but pr&eacute;l&egrave;vement</label>
                            <input type="date" name="withdrawal_start_date" id="withdrawal_start_date" value="{{ old('withdrawal_start_date', $vehicle->withdrawal_start_date?->format('Y-m-d')) }}"
                                   class="w-full h-10 px-3 border border-slate-200 focus:outline-none focus:border-[#2DB56B] focus:ring-0 text-[13px] text-slate-900 transition">
                            @error('withdrawal_start_date')<p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="withdrawal_end_date" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Date fin pr&eacute;l&egrave;vement</label>
                            <input type="date" name="withdrawal_end_date" id="withdrawal_end_date" value="{{ old('withdrawal_end_date', $vehicle->withdrawal_end_date?->format('Y-m-d')) }}"
                                   class="w-full h-10 px-3 border border-slate-200 focus:outline-none focus:border-[#2DB56B] focus:ring-0 text-[13px] text-slate-900 transition">
                            @error('withdrawal_end_date')<p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Dates du contrat --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-x-12 gap-y-5 py-8 border-b border-slate-200">
            <div>
                <p class="text-[13px] font-semibold text-slate-900">Dates du contrat</p>
                <p class="text-[12px] text-slate-400 mt-1 leading-relaxed">Date de d&eacute;but du contrat et date de mise &agrave; disposition du v&eacute;hicule.</p>
            </div>
            <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="contract_start_date" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Date d&eacute;but contrat</label>
                    <input type="date" name="contract_start_date" id="contract_start_date" value="{{ old('contract_start_date', $vehicle->contract_start_date?->format('Y-m-d')) }}"
                           class="w-full h-10 px-3 border border-slate-200 focus:outline-none focus:border-[#2DB56B] focus:ring-0 text-[13px] text-slate-900 transition">
                    @error('contract_start_date')<p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="provision_date" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Date mise &agrave; disposition</label>
                    <input type="date" name="provision_date" id="provision_date" value="{{ old('provision_date', $vehicle->provision_date?->format('Y-m-d')) }}"
                           class="w-full h-10 px-3 border border-slate-200 focus:outline-none focus:border-[#2DB56B] focus:ring-0 text-[13px] text-slate-900 transition">
                    @error('provision_date')<p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-6">
            <a href="{{ route('vehicles.show', $vehicle) }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 text-[13px] font-medium h-10 px-5 hover:bg-slate-50 transition-colors">Annuler</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#2DB56B] hover:bg-[#2AAE64] text-white text-[13px] font-medium h-10 px-5 transition-colors">
                Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection
