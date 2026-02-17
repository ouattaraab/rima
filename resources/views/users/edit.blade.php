@extends('layouts.app')

@section('title', 'Modifier utilisateur')
@section('header', 'Modifier utilisateur')

@section('content')
<div class="mx-auto max-w-4xl">

    {{-- Back link --}}
    <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 text-[12px] text-slate-500 underline hover:text-slate-900 transition mb-6">
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Retour
    </a>

    <form action="{{ route('users.update', $user->id) }}" method="POST" novalidate
        x-data="{
            errors: {},
            validate(e) {
                this.errors = {};
                const f = (id) => (this.$refs[id]?.value || '').trim();
                if (!f('first_name')) this.errors.first_name = 'Le prénom est obligatoire.';
                if (!f('last_name')) this.errors.last_name = 'Le nom est obligatoire.';
                if (!f('username')) this.errors.username = 'Le nom d\'utilisateur est obligatoire.';
                if (!f('email')) this.errors.email = 'L\'email est obligatoire.';
                const pwd = f('password');
                if (pwd && pwd.length < 8) this.errors.password = 'Le mot de passe doit contenir au moins 8 caractères.';
                if (!f('role')) this.errors.role = 'Le rôle est obligatoire.';
                if (!f('organization')) this.errors.organization = 'L\'organisation est obligatoire.';
                if (Object.keys(this.errors).length) { e.preventDefault(); return; }
            }
        }"
        @submit="validate($event)">
        @csrf
        @method('PUT')

        {{-- Section: Identité --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-x-12 gap-y-5 py-8 border-b border-slate-200">
            <div>
                <p class="text-[13px] font-semibold text-slate-900">Identité</p>
                <p class="text-[12px] text-slate-400 mt-1 leading-relaxed">Nom et prénom de l'utilisateur tels qu'ils apparaîtront dans le système.</p>
            </div>
            <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Prénom <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" id="first_name" x-ref="first_name" value="{{ old('first_name', $user->first_name) }}"
                        class="w-full h-10 px-3 border focus:outline-none focus:ring-0 text-[13px] text-slate-900 placeholder-slate-300 transition"
                        :class="errors.first_name ? 'border-red-400 focus:border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'"
                        @input="delete errors.first_name">
                    <p x-show="errors.first_name" x-text="errors.first_name" class="mt-1 text-[12px] text-red-500"></p>
                    @error('first_name')
                        <p class="mt-1 text-[12px] text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="last_name" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Nom <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" id="last_name" x-ref="last_name" value="{{ old('last_name', $user->last_name) }}"
                        class="w-full h-10 px-3 border focus:outline-none focus:ring-0 text-[13px] text-slate-900 placeholder-slate-300 transition"
                        :class="errors.last_name ? 'border-red-400 focus:border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'"
                        @input="delete errors.last_name">
                    <p x-show="errors.last_name" x-text="errors.last_name" class="mt-1 text-[12px] text-red-500"></p>
                    @error('last_name')
                        <p class="mt-1 text-[12px] text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="phone" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Téléphone</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" placeholder="+225 XX XX XX XX XX"
                        class="w-full h-10 px-3 border border-slate-200 focus:outline-none focus:border-[#2DB56B] focus:ring-0 text-[13px] text-slate-900 placeholder-slate-300 transition">
                    @error('phone')
                        <p class="mt-1 text-[12px] text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Section: Compte --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-x-12 gap-y-5 py-8 border-b border-slate-200">
            <div>
                <p class="text-[13px] font-semibold text-slate-900">Compte</p>
                <p class="text-[12px] text-slate-400 mt-1 leading-relaxed">Identifiants de connexion. Laissez le mot de passe vide pour ne pas le modifier.</p>
            </div>
            <div class="lg:col-span-2 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="username" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Nom d'utilisateur <span class="text-red-500">*</span></label>
                        <input type="text" name="username" id="username" x-ref="username" value="{{ old('username', $user->username) }}"
                            class="w-full h-10 px-3 border focus:outline-none focus:ring-0 text-[13px] text-slate-900 placeholder-slate-300 transition"
                            :class="errors.username ? 'border-red-400 focus:border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'"
                            @input="delete errors.username">
                        <p x-show="errors.username" x-text="errors.username" class="mt-1 text-[12px] text-red-500"></p>
                        @error('username')
                            <p class="mt-1 text-[12px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" x-ref="email" value="{{ old('email', $user->email) }}"
                            class="w-full h-10 px-3 border focus:outline-none focus:ring-0 text-[13px] text-slate-900 placeholder-slate-300 transition"
                            :class="errors.email ? 'border-red-400 focus:border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'"
                            @input="delete errors.email">
                        <p x-show="errors.email" x-text="errors.email" class="mt-1 text-[12px] text-red-500"></p>
                        @error('email')
                            <p class="mt-1 text-[12px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label for="password" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Nouveau mot de passe</label>
                    <input type="password" name="password" id="password" x-ref="password" placeholder="Laisser vide pour ne pas modifier"
                        class="w-full h-10 px-3 border focus:outline-none focus:ring-0 text-[13px] text-slate-900 placeholder-slate-300 sm:max-w-sm transition"
                        :class="errors.password ? 'border-red-400 focus:border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'"
                        @input="delete errors.password">
                    <p x-show="errors.password" x-text="errors.password" class="mt-1 text-[12px] text-red-500"></p>
                    @error('password')
                        <p class="mt-1 text-[12px] text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Section: Rôle & Affectation --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-x-12 gap-y-5 py-8 border-b border-slate-200">
            <div>
                <p class="text-[13px] font-semibold text-slate-900">Rôle & affectation</p>
                <p class="text-[12px] text-slate-400 mt-1 leading-relaxed">Définit les permissions et le périmètre d'action de l'utilisateur dans le système.</p>
            </div>
            <div class="lg:col-span-2 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="role" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Rôle <span class="text-red-500">*</span></label>
                        <select name="role" id="role" x-ref="role"
                            class="w-full h-10 px-3 border focus:outline-none focus:ring-0 text-[13px] text-slate-900 bg-white transition"
                            :class="errors.role ? 'border-red-400 focus:border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'"
                            @change="delete errors.role">
                            <option value="">Sélectionner un rôle</option>
                            <option value="agent_cidec" {{ old('role', $user->role) === 'agent_cidec' ? 'selected' : '' }}>Agent CIDEC</option>
                            <option value="supervisor_cidec" {{ old('role', $user->role) === 'supervisor_cidec' ? 'selected' : '' }}>Superviseur CIDEC</option>
                            <option value="supervisor_sodeci" {{ old('role', $user->role) === 'supervisor_sodeci' ? 'selected' : '' }}>Superviseur SODECI</option>
                            <option value="admin_sodeci" {{ old('role', $user->role) === 'admin_sodeci' ? 'selected' : '' }}>Admin SODECI</option>
                        </select>
                        <p x-show="errors.role" x-text="errors.role" class="mt-1 text-[12px] text-red-500"></p>
                        @error('role')
                            <p class="mt-1 text-[12px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="organization" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Organisation <span class="text-red-500">*</span></label>
                        <select name="organization" id="organization" x-ref="organization"
                            class="w-full h-10 px-3 border focus:outline-none focus:ring-0 text-[13px] text-slate-900 bg-white transition"
                            :class="errors.organization ? 'border-red-400 focus:border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'"
                            @change="delete errors.organization">
                            <option value="">Sélectionner</option>
                            <option value="CIDEC" {{ old('organization', $user->organization) === 'CIDEC' ? 'selected' : '' }}>CIDEC</option>
                            <option value="SODECI" {{ old('organization', $user->organization) === 'SODECI' ? 'selected' : '' }}>SODECI</option>
                        </select>
                        <p x-show="errors.organization" x-text="errors.organization" class="mt-1 text-[12px] text-red-500"></p>
                        @error('organization')
                            <p class="mt-1 text-[12px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label for="region" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Région</label>
                    <input type="text" name="region" id="region" value="{{ old('region', $user->region) }}" placeholder="Région d'affectation"
                        class="w-full h-10 px-3 border border-slate-200 focus:outline-none focus:border-[#2DB56B] focus:ring-0 text-[13px] text-slate-900 placeholder-slate-300 sm:max-w-sm transition">
                    @error('region')
                        <p class="mt-1 text-[12px] text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Section: Statut du compte --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-x-12 gap-y-5 py-8 border-b border-slate-200">
            <div>
                <p class="text-[13px] font-semibold text-slate-900">Statut du compte</p>
                <p class="text-[12px] text-slate-400 mt-1 leading-relaxed">Désactiver le compte empêchera l'utilisateur de se connecter.</p>
            </div>
            <div class="lg:col-span-2 flex items-center">
                <input type="hidden" name="is_active" value="0">
                <label for="is_active" class="relative inline-flex items-center cursor-pointer" x-data="{ checked: {{ old('is_active', $user->is_active) ? 'true' : 'false' }} }">
                    <input type="checkbox" name="is_active" value="1" id="is_active" class="sr-only peer" {{ old('is_active', $user->is_active) ? 'checked' : '' }} @change="checked = $el.checked">
                    <div class="w-9 h-5 bg-slate-200 peer-checked:bg-[#2DB56B] transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:w-4 after:h-4 after:transition-all peer-checked:after:translate-x-full"></div>
                    <span class="ml-3 text-[13px] text-slate-600" x-text="checked ? 'Actif' : 'Inactif'"></span>
                </label>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-6">
            <a href="{{ route('users.index') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 text-[13px] font-medium h-10 px-5 hover:bg-slate-50 transition-colors">Annuler</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#2DB56B] hover:bg-[#2AAE64] text-white text-[13px] font-medium h-10 px-5 transition-colors">
                Mettre à jour
            </button>
        </div>
    </form>
</div>
@endsection
