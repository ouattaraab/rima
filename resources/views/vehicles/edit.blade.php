@extends('layouts.app')

@section('title', 'Modifier le vehicule')
@section('header', 'Modifier le vehicule')

@section('content')
<div class="mx-auto max-w-4xl" x-data="{
    errors: {},
    validate(e) {
        this.errors = {};
        const refs = this.$refs;
        if (!(refs.brand?.value || '').trim()) this.errors.brand = 'La marque est obligatoire.';
        if (!(refs.model?.value || '').trim()) this.errors.model = 'Le modele est obligatoire.';
        if (Object.keys(this.errors).length) { e.preventDefault(); return; }
    }
}">

    {{-- Back link --}}
    <a href="{{ route('vehicles.show', $vehicle) }}" class="inline-flex items-center gap-1.5 text-[12px] text-slate-500 underline hover:text-slate-900 transition mb-6">
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Retour a la fiche
    </a>

    <form method="POST" action="{{ route('vehicles.update', $vehicle) }}" novalidate @submit="validate($event)">
        @csrf
        @method('PUT')

        {{-- Motif de modification --}}
        <div class="border-b border-slate-200 pb-6 mb-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div>
                    <p class="text-[13px] font-semibold text-slate-900">Motif de modification</p>
                    <p class="text-[12px] text-slate-400 mt-1">Obligatoire pour toute modification d'une fiche validee</p>
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Motif <span class="text-red-500">*</span></label>
                    <textarea name="modification_reason" rows="2" required placeholder="Raison de la modification..." class="w-full px-3 py-2 border border-slate-200 bg-white text-[13px] text-slate-900 placeholder-slate-300 focus:outline-none focus:border-slate-900 focus:ring-0">{{ old('modification_reason') }}</textarea>
                    @error('modification_reason')<p class="text-[12px] text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Identification --}}
        <div class="border-b border-slate-200 pb-6 mb-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div>
                    <p class="text-[13px] font-semibold text-slate-900">Identification</p>
                    <p class="text-[12px] text-slate-400 mt-1">Informations generales du vehicule</p>
                </div>
                <div class="lg:col-span-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Type</label>
                        <select name="vehicle_type" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                            @foreach(['Auto','Moto'] as $t)
                                <option value="{{ $t }}" {{ old('vehicle_type', $vehicle->vehicle_type) == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Categorie</label>
                        <select name="category" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                            @foreach(['Berline','Pick-up','Utilitaire','Camion','Moto'] as $c)
                                <option value="{{ $c }}" {{ old('category', $vehicle->category) == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Marque</label>
                        <input type="text" name="brand" x-ref="brand" value="{{ old('brand', $vehicle->brand) }}"
                               class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                               :class="errors.brand ? 'border-red-400' : 'border-slate-200 focus:border-slate-900'">
                        <p x-show="errors.brand" x-text="errors.brand" class="text-[12px] text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Modele</label>
                        <input type="text" name="model" x-ref="model" value="{{ old('model', $vehicle->model) }}"
                               class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                               :class="errors.model ? 'border-red-400' : 'border-slate-200 focus:border-slate-900'">
                        <p x-show="errors.model" x-text="errors.model" class="text-[12px] text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Version</label>
                        <input type="text" name="version" value="{{ old('version', $vehicle->version) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Couleur</label>
                        <select name="color" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                            @foreach(['Blanc','Gris','Noir','Autre'] as $c)
                                <option value="{{ $c }}" {{ old('color', $vehicle->color) == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Date mise en circulation</label>
                        <input type="date" name="commissioning_date" value="{{ old('commissioning_date', $vehicle->commissioning_date?->format('Y-m-d')) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Type contrat</label>
                        <select name="contract_type" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                            @foreach(['Sous contrat','Flotte'] as $c)
                                <option value="{{ $c }}" {{ old('contract_type', $vehicle->contract_type) == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Immatriculation --}}
        <div class="border-b border-slate-200 pb-6 mb-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div>
                    <p class="text-[13px] font-semibold text-slate-900">Immatriculation</p>
                    <p class="text-[12px] text-slate-400 mt-1">Numeros d'identification</p>
                </div>
                <div class="lg:col-span-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Immatriculation definitive</label>
                        <input type="text" name="registration_number" value="{{ old('registration_number', $vehicle->registration_number) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Immatriculation provisoire</label>
                        <input type="text" name="temporary_registration" value="{{ old('temporary_registration', $vehicle->temporary_registration) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">N chassis</label>
                        <input type="text" name="chassis_number" value="{{ old('chassis_number', $vehicle->chassis_number) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 font-mono focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Chassis lisible</label>
                        <select name="chassis_readable" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                            <option value="1" {{ old('chassis_readable', $vehicle->chassis_readable) ? 'selected' : '' }}>Oui</option>
                            <option value="0" {{ !old('chassis_readable', $vehicle->chassis_readable) ? 'selected' : '' }}>Non</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Technique --}}
        <div class="border-b border-slate-200 pb-6 mb-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div>
                    <p class="text-[13px] font-semibold text-slate-900">Technique</p>
                    <p class="text-[12px] text-slate-400 mt-1">Caracteristiques techniques</p>
                </div>
                <div class="lg:col-span-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Carburant</label>
                        <select name="fuel_type" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                            @foreach(['Essence','Gasoil','Hybride','Electrique'] as $f)
                                <option value="{{ $f }}" {{ old('fuel_type', $vehicle->fuel_type) == $f ? 'selected' : '' }}>{{ $f }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Transmission</label>
                        <select name="transmission" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                            <option value="">-</option>
                            @foreach(['Automatique','Manuelle'] as $t)
                                <option value="{{ $t }}" {{ old('transmission', $vehicle->transmission) == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Cylindree</label>
                        <input type="number" name="engine_displacement" value="{{ old('engine_displacement', $vehicle->engine_displacement) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Nb places</label>
                        <input type="number" name="seats_count" value="{{ old('seats_count', $vehicle->seats_count) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Charge utile (kg)</label>
                        <input type="number" name="load_capacity" value="{{ old('load_capacity', $vehicle->load_capacity) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Kilometrage</label>
                        <input type="number" name="mileage" value="{{ old('mileage', $vehicle->mileage) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Statut vehicule</label>
                        <select name="status" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                            @foreach(['En service','En reparation','Reforme','Cede'] as $s)
                                <option value="{{ $s }}" {{ old('status', $vehicle->status) == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Structure / CI</label>
                        <input type="text" name="structure_ci" value="{{ old('structure_ci', $vehicle->structure_ci) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Arceaux de securite</label>
                        <select name="has_roll_bars" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                            <option value="">-</option>
                            <option value="1" {{ old('has_roll_bars', $vehicle->has_roll_bars) === true ? 'selected' : '' }}>Oui</option>
                            <option value="0" {{ old('has_roll_bars', $vehicle->has_roll_bars) === false ? 'selected' : '' }}>Non</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Equipements speciaux</label>
                        <input type="text" name="special_equipment" value="{{ old('special_equipment', $vehicle->special_equipment) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Date controle technique</label>
                        <input type="date" name="technical_inspection_date" value="{{ old('technical_inspection_date', $vehicle->technical_inspection_date?->format('Y-m-d')) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                </div>
            </div>
        </div>

        {{-- Assurance --}}
        <div class="border-b border-slate-200 pb-6 mb-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div>
                    <p class="text-[13px] font-semibold text-slate-900">Assurance</p>
                    <p class="text-[12px] text-slate-400 mt-1">Informations reglementaires</p>
                </div>
                <div class="lg:col-span-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Assure</label>
                        <select name="is_insured" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                            <option value="1" {{ old('is_insured', $vehicle->is_insured) ? 'selected' : '' }}>Oui</option>
                            <option value="0" {{ !old('is_insured', $vehicle->is_insured) ? 'selected' : '' }}>Non</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Compagnie</label>
                        <input type="text" name="insurance_company" value="{{ old('insurance_company', $vehicle->insurance_company) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">N police</label>
                        <input type="text" name="policy_number" value="{{ old('policy_number', $vehicle->policy_number) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Type couverture</label>
                        <select name="coverage_type" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                            <option value="">-</option>
                            @foreach(['Tout risque','Tiers'] as $c)
                                <option value="{{ $c }}" {{ old('coverage_type', $vehicle->coverage_type) == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Debut assurance</label>
                        <input type="date" name="insurance_start_date" value="{{ old('insurance_start_date', $vehicle->insurance_start_date?->format('Y-m-d')) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Fin assurance</label>
                        <input type="date" name="insurance_end_date" value="{{ old('insurance_end_date', $vehicle->insurance_end_date?->format('Y-m-d')) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                </div>
            </div>
        </div>

        {{-- Utilisateur --}}
        <div class="border-b border-slate-200 pb-6 mb-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div>
                    <p class="text-[13px] font-semibold text-slate-900">Utilisateur</p>
                    <p class="text-[12px] text-slate-400 mt-1">Identification du conducteur</p>
                </div>
                <div class="lg:col-span-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Direction</label>
                        <input type="text" name="user_direction" value="{{ old('user_direction', $vehicle->user_direction) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Matricule</label>
                        <input type="text" name="user_matricule" value="{{ old('user_matricule', $vehicle->user_matricule) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">N permis de conduire</label>
                        <input type="text" name="user_driver_license" value="{{ old('user_driver_license', $vehicle->user_driver_license) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-0">
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('vehicles.show', $vehicle) }}" class="px-5 py-2 text-[13px] font-medium text-slate-600 bg-white border border-slate-200 rounded-full hover:bg-slate-50 transition">Annuler</a>
            <button type="submit" class="px-5 py-2 text-[13px] font-medium text-white bg-slate-900 hover:bg-black rounded-full transition">Enregistrer les modifications</button>
        </div>
    </form>
</div>
@endsection
