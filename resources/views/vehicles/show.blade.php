@extends('layouts.app')

@section('title', $vehicle->registration_number)

@section('header', $vehicle->registration_number)

@section('content')
@php
    $rejectionReasons = [
        'photo_issue' => 'Probleme photo',
        'registration_error' => 'Erreur immatriculation',
        'data_inconsistency' => 'Incoherence donnees',
        'missing_information' => 'Information manquante',
        'other' => 'Autre',
    ];

    $photoTypeLabels = [
        'front' => 'Face avant',
        'rear' => 'Face arriere',
        'side' => 'Vue laterale',
        'left' => 'Cote gauche',
        'right' => 'Cote droit',
        'interior' => 'Interieur',
        'dashboard' => 'Tableau de bord',
        'chassis' => 'Chassis',
        'FRONT' => 'Face avant',
        'REAR' => 'Face arriere',
        'SIDE' => 'Vue laterale',
        'LEFT' => 'Cote gauche',
        'RIGHT' => 'Cote droit',
        'INTERIOR' => 'Interieur',
        'DASHBOARD' => 'Tableau de bord',
        'CHASSIS' => 'Chassis',
    ];

    $fieldLabels = [
        'registration_number' => 'Immatriculation definitive',
        'temporary_registration' => 'Immatriculation provisoire',
        'chassis_number' => 'Numero de chassis',
        'chassis_readable' => 'Chassis lisible',
        'brand' => 'Marque',
        'model' => 'Modele',
        'version' => 'Version',
        'color' => 'Couleur',
        'vehicle_type' => 'Type de vehicule',
        'category' => 'Categorie',
        'fuel_type' => 'Carburant',
        'transmission' => 'Transmission',
        'engine_displacement' => 'Cylindree',
        'seats_count' => 'Nombre de places',
        'load_capacity' => 'Charge utile',
        'mileage' => 'Kilometrage',
        'status' => 'Statut vehicule',
        'structure_ci' => 'Structure / CI',
        'has_roll_bars' => 'Arceaux de securite',
        'special_equipment' => 'Equipements speciaux',
        'commissioning_date' => 'Date mise en circulation',
        'contract_type' => 'Type de contrat',
        'is_insured' => 'Assure',
        'insurance_company' => 'Compagnie d\'assurance',
        'policy_number' => 'Numero de police',
        'coverage_type' => 'Type de couverture',
        'insurance_start_date' => 'Debut assurance',
        'insurance_end_date' => 'Fin assurance',
        'technical_inspection_date' => 'Date controle technique',
        'user_direction' => 'Direction',
        'user_matricule' => 'Matricule',
        'user_driver_license' => 'Permis de conduire',
        'form_status' => 'Statut fiche',
        'collected_at' => 'Date de collecte',
        'validated_at' => 'Date de validation',
        'validated_by' => 'Valide par',
        'rejection_reason' => 'Motif de rejet',
        'rejection_comment' => 'Commentaire de rejet',
        'financing_mode' => 'Mode de financement',
        'bank_name' => 'Nom de la banque',
        'contract_number' => 'Numero de contrat',
        'contract_start_date' => 'Date debut contrat',
        'provision_date' => 'Date mise a disposition',
        'withdrawal_start_date' => 'Date debut prelevement',
        'withdrawal_end_date' => 'Date fin prelevement',
        'gps_latitude' => 'Latitude GPS',
        'gps_longitude' => 'Longitude GPS',
        'gps_precision' => 'Precision GPS',
        'modification_reason' => 'Motif de modification',
    ];

    $actionLabels = [
        'created' => 'Creation de la fiche',
        'updated' => 'Mise a jour',
        'update_financial' => 'Mise a jour financiere',
        'validated' => 'Validation',
        'validate_vehicle' => 'Validation',
        'rejected' => 'Rejet',
        'reject_vehicle' => 'Rejet',
        'synchronized' => 'Synchronisation',
        'synced' => 'Synchronisation',
        'deleted' => 'Suppression',
        'login' => 'Connexion',
        'logout' => 'Deconnexion',
    ];
@endphp

<div x-data="{
    showValidateModal: false,
    showRejectModal: false,
    rejectErrors: {},
    validateReject(e) {
        this.rejectErrors = {};
        const reason = (this.$refs.rejection_reason?.value || '').trim();
        const comment = (this.$refs.rejection_comment?.value || '').trim();
        if (!reason) this.rejectErrors.reason = 'Le motif est obligatoire.';
        if (!comment) this.rejectErrors.comment = 'Le commentaire est obligatoire.';
        else if (comment.length < 20) this.rejectErrors.comment = 'Le commentaire doit contenir au moins 20 caracteres.';
        if (Object.keys(this.rejectErrors).length) { e.preventDefault(); return; }
    },
    galleryOpen: false,
    galleryIndex: 0,
    galleryPhotos: [],
    galleryZoomed: false,
    openGallery(index) {
        this.galleryIndex = index;
        this.galleryZoomed = false;
        this.galleryOpen = true;
    },
    closeGallery() {
        this.galleryOpen = false;
        this.galleryZoomed = false;
    },
    prevPhoto() {
        this.galleryZoomed = false;
        this.galleryIndex = (this.galleryIndex - 1 + this.galleryPhotos.length) % this.galleryPhotos.length;
    },
    nextPhoto() {
        this.galleryZoomed = false;
        this.galleryIndex = (this.galleryIndex + 1) % this.galleryPhotos.length;
    },
    toggleZoom() {
        this.galleryZoomed = !this.galleryZoomed;
    }
}">

    {{-- Back link --}}
    <div class="mb-6">
        <a href="{{ route('vehicles.index') }}" class="inline-flex items-center gap-1.5 text-[12px] text-slate-400 hover:text-slate-600 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Retour a la liste
        </a>
    </div>

    {{-- Vehicle header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="font-['DM_Sans'] text-2xl font-bold text-slate-900">{{ $vehicle->registration_number ?? 'Sans immatriculation' }}</h2>
                {!! $vehicle->status_badge !!}
                {!! $vehicle->vehicle_status_badge !!}
            </div>
            @if($vehicle->brand || $vehicle->model)
                <p class="mt-1 text-sm text-slate-500">{{ trim(($vehicle->brand ?? '') . ' ' . ($vehicle->model ?? '')) }}</p>
            @endif
        </div>

        {{-- Action buttons --}}
        <div class="flex flex-wrap items-center gap-2">
            @if(
                in_array(auth()->user()->role, ['supervisor_sodeci', 'admin_sodeci'])
                && $vehicle->form_status === 'synchronized'
            )
                <button @click="showValidateModal = true"
                        class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2 text-sm font-medium text-white transition hover:bg-black">
                    Valider
                </button>
                <button @click="showRejectModal = true"
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2 text-sm font-medium text-red-600 transition hover:bg-slate-50">
                    Rejeter
                </button>
            @endif

            @if(auth()->user()->role === 'admin_sodeci')
                <a href="{{ route('vehicles.edit', $vehicle) }}"
                   class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2 text-sm font-medium text-slate-900 transition hover:bg-slate-50">
                    Modifier
                </a>
                <a href="{{ route('vehicles.financial', $vehicle) }}"
                   class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2 text-sm font-medium text-slate-900 transition hover:bg-slate-50">
                    Donnees financieres
                </a>
            @endif
        </div>
    </div>

    {{-- Quality Control --}}
    @if($qualityData)
    @php
        $checks = $qualityData['checks'];
        $missingFields = $qualityData['missingFields'];
        $qualityPct = $qualityData['qualityPct'];
    @endphp
    <div class="bg-white mb-8">
        <div class="px-6 py-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Controle qualite</h3>
                <span class="text-lg font-bold {{ $qualityPct >= 80 ? 'text-emerald-600' : ($qualityPct >= 50 ? 'text-amber-600' : 'text-red-600') }}">{{ $qualityPct }}%</span>
            </div>
            <div class="w-full h-1.5 bg-slate-100 mb-4">
                <div class="h-full {{ $qualityPct >= 80 ? 'bg-emerald-500' : ($qualityPct >= 50 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $qualityPct }}%"></div>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($checks as $check)
                    @if($check['ok'])
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 text-[12px]">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            {{ $check['label'] }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-600 text-[12px] font-medium">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            {{ $check['label'] }}
                        </span>
                    @endif
                @endforeach
            </div>
            @if(count($missingFields) > 0)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-[11px] uppercase tracking-wide text-slate-400 mb-2">Champs manquants</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($missingFields as $field)
                        <span class="inline-flex items-center px-2 py-0.5 text-[11px] bg-red-50 text-red-600">{{ str_replace('_', ' ', $field) }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Main detail container — single white block, sections separated by lines --}}
    <div class="bg-white">

        {{-- 1. Identification --}}
        <div class="px-6 py-5">
            <h3 class="mb-4 text-[13px] font-semibold uppercase tracking-wide text-slate-900">Identification</h3>
            <div class="grid grid-cols-2 gap-x-8 gap-y-4 lg:grid-cols-4">
                @foreach([
                    ['Type de vehicule', $vehicle->vehicle_type],
                    ['Categorie', $vehicle->category],
                    ['Marque', $vehicle->brand],
                    ['Modele', $vehicle->model],
                    ['Version', $vehicle->version],
                    ['Couleur', $vehicle->color],
                    ['Mise en service', $vehicle->commissioning_date?->format('d/m/Y')],
                    ['Type de contrat', $vehicle->contract_type],
                ] as [$label, $value])
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">{{ $label }}</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $value ?? '-' }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-slate-100 mx-6"></div>

        {{-- 2. Immatriculation --}}
        <div class="px-6 py-5">
            <h3 class="mb-4 text-[13px] font-semibold uppercase tracking-wide text-slate-900">Immatriculation</h3>
            <div class="grid grid-cols-2 gap-x-8 gap-y-4 lg:grid-cols-4">
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Numero d'immatriculation</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $vehicle->registration_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Immatriculation provisoire</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $vehicle->temporary_registration ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Numero de chassis</p>
                    <p class="mt-0.5 font-mono text-[13px] text-slate-900">{{ $vehicle->chassis_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Chassis lisible</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $vehicle->chassis_readable !== null ? ($vehicle->chassis_readable ? 'Oui' : 'Non') : '-' }}</p>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 mx-6"></div>

        {{-- 3. Technique --}}
        <div class="px-6 py-5">
            <h3 class="mb-4 text-[13px] font-semibold uppercase tracking-wide text-slate-900">Technique</h3>
            <div class="grid grid-cols-2 gap-x-8 gap-y-4 lg:grid-cols-4">
                @foreach([
                    ['Carburant', $vehicle->fuel_type],
                    ['Transmission', $vehicle->transmission],
                    ['Cylindree', $vehicle->engine_displacement ? $vehicle->engine_displacement . ' cm3' : null],
                    ['Nombre de places', $vehicle->seats_count],
                    ['Charge utile', $vehicle->load_capacity ? $vehicle->load_capacity . ' kg' : null],
                    ['Kilometrage', $vehicle->mileage !== null ? number_format($vehicle->mileage, 0, ',', ' ') . ' km' : null],
                ] as [$label, $value])
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">{{ $label }}</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $value ?? '-' }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-slate-100 mx-6"></div>

        {{-- 4. Statut & Equipement --}}
        <div class="px-6 py-5">
            <h3 class="mb-4 text-[13px] font-semibold uppercase tracking-wide text-slate-900">Statut & Equipement</h3>
            <div class="grid grid-cols-2 gap-x-8 gap-y-4 lg:grid-cols-4">
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Statut</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $vehicle->status ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Structure / CI</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $vehicle->structure_ci ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Arceaux de securite</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $vehicle->has_roll_bars !== null ? ($vehicle->has_roll_bars ? 'Oui' : 'Non') : '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Equipement special</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $vehicle->special_equipment ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Controle technique</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $vehicle->technical_inspection_date?->format('d/m/Y') ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 mx-6"></div>

        {{-- 5. Assurance --}}
        <div class="px-6 py-5">
            <h3 class="mb-4 text-[13px] font-semibold uppercase tracking-wide text-slate-900">Assurance</h3>
            <div class="grid grid-cols-2 gap-x-8 gap-y-4 lg:grid-cols-4">
                @foreach([
                    ['Assure', $vehicle->is_insured !== null ? ($vehicle->is_insured ? 'Oui' : 'Non') : null],
                    ['Compagnie', $vehicle->insurance_company],
                    ['Numero de police', $vehicle->policy_number],
                    ['Couverture', $vehicle->coverage_type],
                    ['Date debut', $vehicle->insurance_start_date?->format('d/m/Y')],
                    ['Date fin', $vehicle->insurance_end_date?->format('d/m/Y')],
                ] as [$label, $value])
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">{{ $label }}</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $value ?? '-' }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-slate-100 mx-6"></div>

        {{-- 6. Utilisateur --}}
        <div class="px-6 py-5">
            <h3 class="mb-4 text-[13px] font-semibold uppercase tracking-wide text-slate-900">Utilisateur</h3>
            <div class="grid grid-cols-2 gap-x-8 gap-y-4 lg:grid-cols-4">
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Direction</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $vehicle->user_direction ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Matricule</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $vehicle->user_matricule ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Permis de conduire</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $vehicle->user_driver_license ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 mx-6"></div>

        {{-- 7. Financier --}}
        <div class="px-6 py-5">
            <h3 class="mb-4 text-[13px] font-semibold uppercase tracking-wide text-slate-900">Financier</h3>
            <div class="grid grid-cols-2 gap-x-8 gap-y-4 lg:grid-cols-4">
                @foreach([
                    ['Mode de financement', $vehicle->financing_mode],
                    ['Banque', $vehicle->bank_name],
                    ['Numero de contrat', $vehicle->contract_number],
                    ['Debut prelevement', $vehicle->withdrawal_start_date?->format('d/m/Y')],
                    ['Fin prelevement', $vehicle->withdrawal_end_date?->format('d/m/Y')],
                    ['Debut contrat', $vehicle->contract_start_date?->format('d/m/Y')],
                    ['Mise a disposition', $vehicle->provision_date?->format('d/m/Y')],
                ] as [$label, $value])
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">{{ $label }}</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $value ?? '-' }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-slate-100 mx-6"></div>

        {{-- 8. Collecte & GPS --}}
        <div class="px-6 py-5">
            <h3 class="mb-4 text-[13px] font-semibold uppercase tracking-wide text-slate-900">Collecte</h3>
            <div class="grid grid-cols-2 gap-x-8 gap-y-4 lg:grid-cols-4">
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Agent collecteur</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $vehicle->collector?->full_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Date de collecte</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $vehicle->collected_at?->format('d/m/Y H:i') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">GPS</p>
                    <p class="mt-0.5 font-mono text-[13px] text-slate-900">
                        @if($vehicle->gps_latitude && $vehicle->gps_longitude)
                            {{ number_format($vehicle->gps_latitude, 5) }}, {{ number_format($vehicle->gps_longitude, 5) }}
                            @if($vehicle->gps_accuracy)
                                <span class="text-slate-400 text-[11px] ml-1">(±{{ round($vehicle->gps_accuracy) }}m)</span>
                            @endif
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 mx-6"></div>

        {{-- 9. Validation --}}
        <div class="px-6 py-5">
            <h3 class="mb-4 text-[13px] font-semibold uppercase tracking-wide text-slate-900">Validation</h3>
            <div class="grid grid-cols-2 gap-x-8 gap-y-4 lg:grid-cols-4">
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Validateur</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $vehicle->validator?->full_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Date de validation</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $vehicle->validated_at?->format('d/m/Y H:i') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Motif de rejet</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $vehicle->rejection_reason ? ($rejectionReasons[$vehicle->rejection_reason] ?? $vehicle->rejection_reason) : '-' }}</p>
                </div>
                <div class="lg:col-span-4">
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Commentaire de rejet</p>
                    <p class="mt-0.5 text-[13px] text-slate-900">{{ $vehicle->rejection_comment ?? '-' }}</p>
                </div>
            </div>
        </div>

    </div>

    {{-- Photos --}}
    @if($vehicle->photos && $vehicle->photos->count())
        <div class="bg-white mt-8"
             x-init="galleryPhotos = {{ Js::from($vehicle->photos->map(fn($p) => ['url' => $p->url, 'type' => $photoTypeLabels[$p->photo_type] ?? $p->photo_type ?? 'Photo vehicule'])->values()) }}">
            <div class="px-6 py-5">
                <div class="flex items-center gap-2 mb-4">
                    <h3 class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Photos</h3>
                    <span class="text-[11px] text-slate-400">{{ $vehicle->photos->count() }}</span>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($vehicle->photos as $index => $photo)
                        <div class="relative overflow-hidden bg-slate-50 cursor-pointer group"
                             @click="openGallery({{ $index }})">
                            <div class="aspect-square relative">
                                <img src="{{ $photo->url }}"
                                     alt="{{ $photoTypeLabels[$photo->photo_type] ?? $photo->photo_type ?? 'Photo vehicule' }}"
                                     class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105"
                                     loading="lazy">
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-200 flex items-center justify-center">
                                    <svg class="w-7 h-7 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6"/>
                                    </svg>
                                </div>
                            </div>
                            @if($photo->photo_type)
                                <div class="px-3 py-2">
                                    <p class="text-[11px] uppercase tracking-wide text-slate-400">{{ $photoTypeLabels[$photo->photo_type] ?? $photo->photo_type }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Photo Gallery Modal --}}
    <template x-teleport="body">
        <div x-show="galleryOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[60] flex items-center justify-center bg-black/90"
             @keydown.escape.window="closeGallery()"
             @keydown.left.window="galleryOpen && prevPhoto()"
             @keydown.right.window="galleryOpen && nextPhoto()"
             x-cloak>

            <button @click="closeGallery()"
                    class="absolute top-4 right-4 z-10 p-2 text-white/70 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <div class="absolute top-4 left-4 z-10 text-white/70 text-sm font-medium">
                <span x-text="(galleryIndex + 1) + ' / ' + galleryPhotos.length"></span>
            </div>

            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-10">
                <span class="inline-flex items-center px-3 py-1 bg-black/60 text-white text-[12px] uppercase tracking-wide"
                      x-text="galleryPhotos[galleryIndex]?.type"></span>
            </div>

            <button x-show="galleryPhotos.length > 1"
                    @click.stop="prevPhoto()"
                    class="absolute left-4 top-1/2 -translate-y-1/2 z-10 p-2 text-white/70 hover:text-white transition-colors bg-black/30 hover:bg-black/50">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            <button x-show="galleryPhotos.length > 1"
                    @click.stop="nextPhoto()"
                    class="absolute right-4 top-1/2 -translate-y-1/2 z-10 p-2 text-white/70 hover:text-white transition-colors bg-black/30 hover:bg-black/50">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <div class="flex items-center justify-center w-full h-full p-16"
                 @click.self="closeGallery()">
                <img :src="galleryPhotos[galleryIndex]?.url"
                     :alt="galleryPhotos[galleryIndex]?.type"
                     @click.stop="toggleZoom()"
                     class="max-h-full transition-all duration-300"
                     :class="galleryZoomed ? 'max-w-none cursor-zoom-out scale-150' : 'max-w-full cursor-zoom-in'"
                     style="object-fit: contain;">
            </div>
        </div>
    </template>

    {{-- History timeline --}}
    @if($vehicle->histories && $vehicle->histories->count())
        <div class="bg-white mt-8">
            <div class="px-6 py-5">
                <h3 class="mb-5 text-[13px] font-semibold uppercase tracking-wide text-slate-900">Historique</h3>
                <div class="relative">
                    <div class="absolute left-[7px] top-0 bottom-0 w-px bg-slate-100"></div>

                    <div class="space-y-5">
                        @foreach($vehicle->histories as $hIdx => $history)
                            @php
                                $hasChanges = !empty($history->old_values) && !empty($history->new_values) && is_array($history->old_values) && is_array($history->new_values);
                                $changedFields = $hasChanges ? array_unique(array_merge(array_keys($history->old_values), array_keys($history->new_values))) : [];
                            @endphp
                            <div class="relative flex gap-4 pl-8" x-data="{ expanded: false }">
                                <div class="absolute left-0 top-1.5 h-[14px] w-[14px] rounded-full border-2 border-white bg-slate-900"></div>

                                <div class="flex-1">
                                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="text-[13px] text-slate-900">{{ $history->description ?? ($actionLabels[$history->action] ?? $history->action) ?? '-' }}</p>
                                        <time class="text-[11px] text-slate-400">{{ $history->created_at?->format('d/m/Y H:i') }}</time>
                                    </div>
                                    @if($history->user)
                                        <p class="mt-0.5 text-[11px] text-slate-400">Par {{ $history->user->full_name }}</p>
                                    @endif
                                    @if($history->comment)
                                        <p class="mt-1.5 text-[13px] italic text-slate-500">{{ $history->comment }}</p>
                                    @endif

                                    @if($hasChanges && count($changedFields) > 0)
                                        <button @click="expanded = !expanded" class="mt-2 inline-flex items-center gap-1.5 text-[12px] font-medium text-slate-400 hover:text-slate-900 transition-colors">
                                            <svg :class="expanded && 'rotate-90'" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                            <span x-text="expanded ? 'Masquer' : '{{ count($changedFields) }} champ(s) modifie(s)'"></span>
                                        </button>

                                        <div x-show="expanded" x-collapse x-cloak class="mt-2 overflow-hidden">
                                            <table class="w-full text-[12px]">
                                                <thead>
                                                    <tr class="border-b border-slate-100">
                                                        <th class="text-left px-3 py-1.5 font-medium text-slate-400 uppercase tracking-wide">Champ</th>
                                                        <th class="text-left px-3 py-1.5 font-medium text-slate-400 uppercase tracking-wide">Avant</th>
                                                        <th class="text-left px-3 py-1.5 font-medium text-slate-400 uppercase tracking-wide">Apres</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-50">
                                                    @foreach($changedFields as $field)
                                                        @php
                                                            $oldVal = $history->old_values[$field] ?? null;
                                                            $newVal = $history->new_values[$field] ?? null;
                                                            if (is_bool($oldVal)) $oldVal = $oldVal ? 'Oui' : 'Non';
                                                            if (is_bool($newVal)) $newVal = $newVal ? 'Oui' : 'Non';
                                                            if (is_null($oldVal)) $oldVal = '-';
                                                            if (is_null($newVal)) $newVal = '-';
                                                            if (is_array($oldVal)) $oldVal = json_encode($oldVal);
                                                            if (is_array($newVal)) $newVal = json_encode($newVal);
                                                            // Format ISO datetimes to d/m/Y H:i:s
                                                            if (is_string($oldVal) && preg_match('/^\d{4}-\d{2}-\d{2}(T|\s)/', $oldVal)) {
                                                                try { $oldVal = \Carbon\Carbon::parse($oldVal)->format('d/m/Y H:i:s'); } catch (\Exception $e) {}
                                                            }
                                                            if (is_string($newVal) && preg_match('/^\d{4}-\d{2}-\d{2}(T|\s)/', $newVal)) {
                                                                try { $newVal = \Carbon\Carbon::parse($newVal)->format('d/m/Y H:i:s'); } catch (\Exception $e) {}
                                                            }
                                                            // Format simple YYYY-MM-DD dates to d/m/Y
                                                            if (is_string($oldVal) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $oldVal)) {
                                                                try { $oldVal = \Carbon\Carbon::parse($oldVal)->format('d/m/Y'); } catch (\Exception $e) {}
                                                            }
                                                            if (is_string($newVal) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $newVal)) {
                                                                try { $newVal = \Carbon\Carbon::parse($newVal)->format('d/m/Y'); } catch (\Exception $e) {}
                                                            }
                                                        @endphp
                                                        <tr>
                                                            <td class="px-3 py-1.5 text-slate-600 font-medium">{{ $fieldLabels[$field] ?? str_replace('_', ' ', $field) }}</td>
                                                            <td class="px-3 py-1.5 text-red-600">{{ $oldVal }}</td>
                                                            <td class="px-3 py-1.5 text-emerald-600">{{ $newVal }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Validate Modal --}}
    <template x-teleport="body">
        <div x-show="showValidateModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-4"
             @keydown.escape.window="showValidateModal = false"
             x-cloak>
            <div @click.outside="showValidateModal = false"
                 x-show="showValidateModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-md bg-white p-6">
                <div class="mb-5">
                    <h3 class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Valider le vehicule</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $vehicle->registration_number }}</p>
                </div>

                <form method="POST" action="{{ route('vehicles.validate', $vehicle) }}">
                    @csrf
                    <div class="mb-4">
                        <label for="validate_comment" class="mb-1.5 block text-[11px] uppercase tracking-wide text-slate-400">Commentaire (optionnel)</label>
                        <textarea id="validate_comment"
                                  name="comment"
                                  rows="3"
                                  placeholder="Ajouter un commentaire..."
                                  class="block w-full rounded-none border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-slate-900 focus:outline-none focus:ring-0"></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button"
                                @click="showValidateModal = false"
                                class="rounded-full border border-slate-200 bg-white px-5 py-2 text-sm font-medium text-slate-900 transition hover:bg-slate-50">
                            Annuler
                        </button>
                        <button type="submit"
                                class="rounded-full bg-slate-900 px-5 py-2 text-sm font-medium text-white transition hover:bg-black">
                            Confirmer la validation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- Reject Modal --}}
    <template x-teleport="body">
        <div x-show="showRejectModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-4"
             @keydown.escape.window="showRejectModal = false"
             x-cloak>
            <div @click.outside="showRejectModal = false"
                 x-show="showRejectModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-md bg-white p-6">
                <div class="mb-5">
                    <h3 class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Rejeter le vehicule</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $vehicle->registration_number }}</p>
                </div>

                <form method="POST" action="{{ route('vehicles.reject', $vehicle) }}" novalidate @submit="validateReject($event)">
                    @csrf
                    <div class="mb-4">
                        <label for="rejection_reason" class="mb-1.5 block text-[11px] uppercase tracking-wide text-slate-400">Motif du rejet <span class="text-red-600">*</span></label>
                        <select name="rejection_reason"
                                id="rejection_reason"
                                x-ref="rejection_reason"
                                class="block w-full rounded-none border bg-white py-2 pl-3 pr-8 text-sm text-slate-900 focus:outline-none focus:ring-0 transition"
                                :class="rejectErrors.reason ? 'border-red-400 focus:border-red-400' : 'border-slate-200 focus:border-slate-900'"
                                @change="delete rejectErrors.reason">
                            <option value="">Selectionnez un motif</option>
                            @foreach($rejectionReasons as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p x-show="rejectErrors.reason" x-text="rejectErrors.reason" class="mt-1 text-[12px] text-red-500"></p>
                    </div>
                    <div class="mb-4">
                        <label for="reject_comment" class="mb-1.5 block text-[11px] uppercase tracking-wide text-slate-400">Commentaire <span class="text-red-600">*</span> <span class="normal-case text-slate-300">(min. 20 caracteres)</span></label>
                        <textarea id="reject_comment"
                                  name="rejection_comment"
                                  x-ref="rejection_comment"
                                  rows="3"
                                  placeholder="Preciser le motif du rejet (min. 20 caracteres)..."
                                  class="block w-full rounded-none border bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0 transition"
                                  :class="rejectErrors.comment ? 'border-red-400 focus:border-red-400' : 'border-slate-200 focus:border-slate-900'"
                                  @input="delete rejectErrors.comment"></textarea>
                        <p x-show="rejectErrors.comment" x-text="rejectErrors.comment" class="mt-1 text-[12px] text-red-500"></p>
                        @error('rejection_comment')
                            <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button"
                                @click="showRejectModal = false"
                                class="rounded-full border border-slate-200 bg-white px-5 py-2 text-sm font-medium text-slate-900 transition hover:bg-slate-50">
                            Annuler
                        </button>
                        <button type="submit"
                                class="rounded-full border border-slate-200 bg-white px-5 py-2 text-sm font-medium text-red-600 transition hover:bg-slate-50">
                            Confirmer le rejet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection
