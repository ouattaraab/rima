@extends('layouts.app')

@section('title', 'Modifier le véhicule')
@section('header', 'Modifier le véhicule')

@section('content')
<div class="mx-auto max-w-4xl" x-data="{
    vehicleType: '{{ old('vehicle_type', $vehicle->vehicle_type) }}',
    category: '{{ old('category', $vehicle->category) }}',
    status: '{{ old('status', $vehicle->status) }}',
    isInsured: {{ old('is_insured', $vehicle->is_insured) ? 'true' : 'false' }},
    errors: {},
    validate(e) {
        this.errors = {};
        const refs = this.$refs;

        // Marque & modele obligatoires
        if (!(refs.brand?.value || '').trim()) this.errors.brand = 'La marque est obligatoire.';
        if (!(refs.model?.value || '').trim()) this.errors.model = 'Le modèle est obligatoire.';

        // Kilometrage > 0
        if (refs.mileage && refs.mileage.value !== '' && parseInt(refs.mileage.value) < 1) {
            this.errors.mileage = 'Le kilométrage doit être strictement positif.';
        }

        // Nb places > 0
        if (refs.seats_count && refs.seats_count.value !== '' && parseInt(refs.seats_count.value) < 1) {
            this.errors.seats_count = 'Le nombre de places doit être supérieur à 0.';
        }

        // Charge utile > 0 si Camion/Pick-up
        if (['Camion', 'Pick-up'].includes(this.category)) {
            if (!refs.load_capacity?.value || parseInt(refs.load_capacity.value) < 1) {
                this.errors.load_capacity = 'La charge utile est obligatoire pour les Camions et Pick-up.';
            }
        }

        // Transmission obligatoire si Auto
        if (this.vehicleType === 'Auto' && !refs.transmission?.value) {
            this.errors.transmission = 'La transmission est obligatoire pour les véhicules de type Auto.';
        }

        // Structure CI obligatoire si En service / En reparation
        if (['En service', 'En reparation'].includes(this.status) && !(refs.structure_ci?.value || '').trim()) {
            this.errors.structure_ci = 'Le centre d\'imputation est obligatoire pour les véhicules en service ou en réparation.';
        }

        // Assurance obligatoire si En service
        if (this.status === 'En service' && !this.isInsured) {
            this.errors.is_insured = 'L\'assurance est obligatoire pour les véhicules en service.';
        }

        // Details assurance si assure
        if (this.isInsured) {
            if (!(refs.insurance_company?.value || '').trim()) this.errors.insurance_company = 'La compagnie d\'assurance est obligatoire.';
            if (!(refs.insurance_company?.value || '').trim()) this.errors.insurance_company = 'La compagnie d\'assurance est obligatoire.';
            if (!(refs.policy_number?.value || '').trim()) this.errors.policy_number = 'Le numéro de police est obligatoire.';
            if (!refs.insurance_start_date?.value) this.errors.insurance_start_date = 'La date de début d\'assurance est obligatoire.';
            if (!refs.insurance_end_date?.value) this.errors.insurance_end_date = 'La date de fin d\'assurance est obligatoire.';
        }

        // Dates croisees : fin assurance > debut assurance
        if (refs.insurance_start_date?.value && refs.insurance_end_date?.value) {
            if (refs.insurance_end_date.value <= refs.insurance_start_date.value) {
                this.errors.insurance_end_date = 'La date de fin d\'assurance doit être postérieure à la date de début.';
            }
        }

        // Dates croisees : visite technique >= mise en circulation
        if (refs.commissioning_date?.value && refs.technical_inspection_date?.value) {
            if (refs.technical_inspection_date.value < refs.commissioning_date.value) {
                this.errors.technical_inspection_date = 'La date de contrôle technique ne peut pas être antérieure à la mise en circulation.';
            }
        }

        // Dates croisees : debut assurance >= mise en circulation
        if (refs.commissioning_date?.value && refs.insurance_start_date?.value) {
            if (refs.insurance_start_date.value < refs.commissioning_date.value) {
                this.errors.insurance_start_date = 'La date de début d\'assurance ne peut pas être antérieure à la mise en circulation.';
            }
        }

        // Matricule : exactement 7 caracteres alphanumeriques
        if (refs.user_matricule?.value && !/^[A-Za-z0-9]{7}$/.test(refs.user_matricule.value)) {
            this.errors.user_matricule = 'Le matricule doit comporter exactement 7 caractères alphanumériques.';
        }

        // Arceaux obligatoires si Pick-up
        if (this.category === 'Pick-up' && !refs.has_roll_bars?.value) {
            this.errors.has_roll_bars = 'L\'indication des arceaux est obligatoire pour les Pick-up.';
        }

        // Version uniquement pour Berline
        if (refs.version?.value && this.category !== 'Berline') {
            this.errors.version = 'La version ne concerne que les Berlines.';
        }

        // Equipements speciaux uniquement Camion
        if (refs.special_equipment?.value && this.category !== 'Camion') {
            this.errors.special_equipment = 'Les équipements spéciaux ne concernent que les Camions.';
        }

        // Transmission interdite pour Moto
        if (this.vehicleType === 'Moto' && refs.transmission?.value) {
            this.errors.transmission = 'La transmission n\'est pas applicable pour les Motos.';
        }

        if (Object.keys(this.errors).length) { e.preventDefault(); return; }
    }
}">

    {{-- Back link --}}
    <a href="{{ route('vehicles.show', $vehicle) }}" class="inline-flex items-center gap-1.5 text-[12px] text-slate-500 underline hover:text-slate-900 transition mb-6">
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Retour à la fiche
    </a>

    <form method="POST" action="{{ route('vehicles.update', $vehicle) }}" novalidate @submit="validate($event)">
        @csrf
        @method('PUT')

        @if($errors->any())
        <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200">
            <p class="text-[13px] font-semibold text-red-700 mb-2">Des erreurs de cohérence ont été détectées :</p>
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li class="text-[12px] text-red-600">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Motif de modification --}}
        <div class="border-b border-slate-200 pb-6 mb-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div>
                    <p class="text-[13px] font-semibold text-slate-900">Motif de modification</p>
                    <p class="text-[12px] text-slate-400 mt-1">Obligatoire pour toute modification d'une fiche validée</p>
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Motif <span class="text-red-500">*</span></label>
                    <textarea name="modification_reason" rows="2" required maxlength="500" placeholder="Raison de la modification..." class="w-full px-3 py-2 border border-slate-200 bg-white text-[13px] text-slate-900 placeholder-slate-300 focus:outline-none focus:border-[#2DB56B] focus:ring-0">{{ old('modification_reason') }}</textarea>
                    @error('modification_reason')<p class="text-[12px] text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Identification --}}
        <div class="border-b border-slate-200 pb-6 mb-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div>
                    <p class="text-[13px] font-semibold text-slate-900">Identification</p>
                    <p class="text-[12px] text-slate-400 mt-1">Informations générales du véhicule</p>
                </div>
                <div class="lg:col-span-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Type</label>
                        <select name="vehicle_type" x-model="vehicleType" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-[#2DB56B] focus:ring-0">
                            @foreach(['Auto','Moto'] as $t)
                                <option value="{{ $t }}" {{ old('vehicle_type', $vehicle->vehicle_type) == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Catégorie</label>
                        <select name="category" x-model="category" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-[#2DB56B] focus:ring-0">
                            @foreach(['Berline','Pick-up','Utilitaire','Camion','Moto'] as $c)
                                <option value="{{ $c }}" {{ old('category', $vehicle->category) == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Marque <span class="text-red-500">*</span></label>
                        <input type="text" name="brand" x-ref="brand" maxlength="50" value="{{ old('brand', $vehicle->brand) }}"
                               class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                               :class="errors.brand ? 'border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'">
                        <p x-show="errors.brand" x-text="errors.brand" class="text-[12px] text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Modèle <span class="text-red-500">*</span></label>
                        <input type="text" name="model" x-ref="model" maxlength="100" value="{{ old('model', $vehicle->model) }}"
                               class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                               :class="errors.model ? 'border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'">
                        <p x-show="errors.model" x-text="errors.model" class="text-[12px] text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Version</label>
                        <input type="text" name="version" x-ref="version" maxlength="50" value="{{ old('version', $vehicle->version) }}"
                               class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                               :class="errors.version ? 'border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'">
                        <p x-show="errors.version" x-text="errors.version" class="text-[12px] text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Couleur</label>
                        <select name="color" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-[#2DB56B] focus:ring-0">
                            @foreach(['Blanc','Noir','Gris','Bleu','Rouge','Vert','Jaune','Beige','Marron','Autre'] as $c)
                                <option value="{{ $c }}" {{ old('color', $vehicle->color) == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Date mise en circulation</label>
                        <input type="date" name="commissioning_date" x-ref="commissioning_date" max="{{ date('Y-m-d') }}" value="{{ old('commissioning_date', $vehicle->commissioning_date?->format('Y-m-d')) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-[#2DB56B] focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Type contrat</label>
                        <select name="contract_type" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-[#2DB56B] focus:ring-0">
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
                    <p class="text-[12px] text-slate-400 mt-1">Numéros d'identification</p>
                </div>
                <div class="lg:col-span-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Immatriculation définitive</label>
                        <input type="text" name="registration_number" maxlength="10" pattern="[A-Za-z0-9\s\-]+" value="{{ old('registration_number', $vehicle->registration_number) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-[#2DB56B] focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Immatriculation provisoire</label>
                        <input type="text" name="temporary_registration" maxlength="10" pattern="[A-Za-z0-9\s\-]+" value="{{ old('temporary_registration', $vehicle->temporary_registration) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-[#2DB56B] focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">N chassis</label>
                        <input type="text" name="chassis_number" maxlength="30" pattern="[A-Za-z0-9]+" value="{{ old('chassis_number', $vehicle->chassis_number) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 font-mono focus:outline-none focus:border-[#2DB56B] focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Chassis lisible</label>
                        <select name="chassis_readable" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-[#2DB56B] focus:ring-0">
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
                    <p class="text-[12px] text-slate-400 mt-1">Caractéristiques techniques</p>
                </div>
                <div class="lg:col-span-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Carburant</label>
                        <select name="fuel_type" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-[#2DB56B] focus:ring-0">
                            @foreach(['Essence','Gasoil','Hybride','Electrique'] as $f)
                                <option value="{{ $f }}" {{ old('fuel_type', $vehicle->fuel_type) == $f ? 'selected' : '' }}>{{ $f }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Transmission</label>
                        <select name="transmission" x-ref="transmission"
                                class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                                :class="errors.transmission ? 'border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'">
                            <option value="">-</option>
                            @foreach(['Automatique','Manuelle'] as $t)
                                <option value="{{ $t }}" {{ old('transmission', $vehicle->transmission) == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                        <p x-show="errors.transmission" x-text="errors.transmission" class="text-[12px] text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Cylindrée</label>
                        <input type="number" name="engine_displacement" min="50" max="99999" value="{{ old('engine_displacement', $vehicle->engine_displacement) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-[#2DB56B] focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Nb places</label>
                        <input type="number" name="seats_count" x-ref="seats_count" min="1" max="99" value="{{ old('seats_count', $vehicle->seats_count) }}"
                               class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                               :class="errors.seats_count ? 'border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'">
                        <p x-show="errors.seats_count" x-text="errors.seats_count" class="text-[12px] text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Charge utile (kg)</label>
                        <input type="number" name="load_capacity" x-ref="load_capacity" min="1" max="99999" value="{{ old('load_capacity', $vehicle->load_capacity) }}"
                               class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                               :class="errors.load_capacity ? 'border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'">
                        <p x-show="errors.load_capacity" x-text="errors.load_capacity" class="text-[12px] text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Kilométrage</label>
                        <input type="number" name="mileage" x-ref="mileage" min="1" max="9999999" value="{{ old('mileage', $vehicle->mileage) }}"
                               class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                               :class="errors.mileage ? 'border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'">
                        <p x-show="errors.mileage" x-text="errors.mileage" class="text-[12px] text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Statut véhicule</label>
                        <select name="status" x-model="status" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-[#2DB56B] focus:ring-0">
                            @foreach(['En service','En reparation','Reforme','Cede'] as $s)
                                <option value="{{ $s }}" {{ old('status', $vehicle->status) == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Structure / CI</label>
                        <input type="text" name="structure_ci" x-ref="structure_ci" maxlength="10" value="{{ old('structure_ci', $vehicle->structure_ci) }}"
                               class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                               :class="errors.structure_ci ? 'border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'">
                        <p x-show="errors.structure_ci" x-text="errors.structure_ci" class="text-[12px] text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Arceaux de sécurité</label>
                        <select name="has_roll_bars" x-ref="has_roll_bars"
                                class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                                :class="errors.has_roll_bars ? 'border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'">
                            <option value="">-</option>
                            <option value="1" {{ old('has_roll_bars', $vehicle->has_roll_bars) === true ? 'selected' : '' }}>Oui</option>
                            <option value="0" {{ old('has_roll_bars', $vehicle->has_roll_bars) === false ? 'selected' : '' }}>Non</option>
                        </select>
                        <p x-show="errors.has_roll_bars" x-text="errors.has_roll_bars" class="text-[12px] text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Équipements spéciaux</label>
                        <input type="text" name="special_equipment" x-ref="special_equipment" maxlength="100" value="{{ old('special_equipment', $vehicle->special_equipment) }}"
                               class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                               :class="errors.special_equipment ? 'border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'">
                        <p x-show="errors.special_equipment" x-text="errors.special_equipment" class="text-[12px] text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Date contrôle technique</label>
                        <input type="date" name="technical_inspection_date" x-ref="technical_inspection_date" max="{{ date('Y-m-d') }}" value="{{ old('technical_inspection_date', $vehicle->technical_inspection_date?->format('Y-m-d')) }}"
                               class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                               :class="errors.technical_inspection_date ? 'border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'">
                        <p x-show="errors.technical_inspection_date" x-text="errors.technical_inspection_date" class="text-[12px] text-red-500 mt-1"></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Assurance --}}
        <div class="border-b border-slate-200 pb-6 mb-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div>
                    <p class="text-[13px] font-semibold text-slate-900">Assurance</p>
                    <p class="text-[12px] text-slate-400 mt-1">Informations réglementaires</p>
                </div>
                <div class="lg:col-span-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Assuré</label>
                        <select name="is_insured" x-model="isInsured" @change="isInsured = ($event.target.value === '1' || $event.target.value === true)"
                                class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                                :class="errors.is_insured ? 'border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'">
                            <option value="1" {{ old('is_insured', $vehicle->is_insured) ? 'selected' : '' }}>Oui</option>
                            <option value="0" {{ !old('is_insured', $vehicle->is_insured) ? 'selected' : '' }}>Non</option>
                        </select>
                        <p x-show="errors.is_insured" x-text="errors.is_insured" class="text-[12px] text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Compagnie</label>
                        <input type="text" name="insurance_company" x-ref="insurance_company" maxlength="50" value="{{ old('insurance_company', $vehicle->insurance_company) }}"
                               class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                               :class="errors.insurance_company ? 'border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'">
                        <p x-show="errors.insurance_company" x-text="errors.insurance_company" class="text-[12px] text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">N police</label>
                        <input type="text" name="policy_number" x-ref="policy_number" maxlength="30" value="{{ old('policy_number', $vehicle->policy_number) }}"
                               class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                               :class="errors.policy_number ? 'border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'">
                        <p x-show="errors.policy_number" x-text="errors.policy_number" class="text-[12px] text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Type couverture</label>
                        <select name="coverage_type" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-[#2DB56B] focus:ring-0">
                            <option value="">-</option>
                            @foreach(['Tout risque','Tiers'] as $c)
                                <option value="{{ $c }}" {{ old('coverage_type', $vehicle->coverage_type) == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Début assurance</label>
                        <input type="date" name="insurance_start_date" x-ref="insurance_start_date" value="{{ old('insurance_start_date', $vehicle->insurance_start_date?->format('Y-m-d')) }}"
                               class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                               :class="errors.insurance_start_date ? 'border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'">
                        <p x-show="errors.insurance_start_date" x-text="errors.insurance_start_date" class="text-[12px] text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Fin assurance</label>
                        <input type="date" name="insurance_end_date" x-ref="insurance_end_date" value="{{ old('insurance_end_date', $vehicle->insurance_end_date?->format('Y-m-d')) }}"
                               class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                               :class="errors.insurance_end_date ? 'border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'">
                        <p x-show="errors.insurance_end_date" x-text="errors.insurance_end_date" class="text-[12px] text-red-500 mt-1"></p>
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
                        <input type="text" name="user_direction" maxlength="100" value="{{ old('user_direction', $vehicle->user_direction) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-[#2DB56B] focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Matricule</label>
                        <input type="text" name="user_matricule" x-ref="user_matricule" maxlength="7" pattern="[A-Za-z0-9]{7}" value="{{ old('user_matricule', $vehicle->user_matricule) }}"
                               class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                               :class="errors.user_matricule ? 'border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'">
                        <p x-show="errors.user_matricule" x-text="errors.user_matricule" class="text-[12px] text-red-500 mt-1"></p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">N permis de conduire</label>
                        <input type="text" name="user_driver_license" maxlength="50" value="{{ old('user_driver_license', $vehicle->user_driver_license) }}" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:border-[#2DB56B] focus:ring-0">
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('vehicles.show', $vehicle) }}" class="px-5 py-2 text-[13px] font-medium text-slate-600 bg-white border border-slate-200 rounded-full hover:bg-slate-50 transition">Annuler</a>
            <button type="submit" class="px-5 py-2 text-[13px] font-medium text-white bg-[#2DB56B] hover:bg-[#2AAE64] rounded-full transition">Enregistrer les modifications</button>
        </div>
    </form>
</div>
@endsection
