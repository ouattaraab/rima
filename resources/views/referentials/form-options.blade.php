@extends('layouts.app')

@section('title', 'Options formulaire')
@section('header', 'Options formulaire')

@section('content')
<div x-data="{
    showAddModal: false,
    showEditModal: false,
    addErrors: {},
    editErrors: {},
    editItem: { id: '', type: '', value: '', parent_type: '', parent_value: '', sort_order: 0, is_active: true },
    validateAdd(e) {
        this.addErrors = {};
        if (!this.$refs.add_type.value) this.addErrors.type = 'Le type est obligatoire.';
        if (!this.$refs.add_value.value.trim()) this.addErrors.value = 'La valeur est obligatoire.';
        if (Object.keys(this.addErrors).length) { e.preventDefault(); return; }
    },
    validateEdit(e) {
        this.editErrors = {};
        if (!this.editItem.value.trim()) this.editErrors.value = 'La valeur est obligatoire.';
        if (Object.keys(this.editErrors).length) { e.preventDefault(); return; }
    },
    openEdit(item) {
        this.editErrors = {};
        this.editItem = {
            id: item.id,
            type: item.type,
            value: item.value,
            parent_type: item.parent_type || '',
            parent_value: item.parent_value || '',
            sort_order: item.sort_order || 0,
            is_active: item.is_active
        };
        this.showEditModal = true;
    },
    typeLabels: {
        vehicle_type: 'Type de vehicule',
        category: 'Categorie',
        fuel_type: 'Carburant',
        transmission: 'Transmission',
        status: 'Statut vehicule',
        contract_type: 'Type de contrat',
        coverage_type: 'Type de couverture',
        color: 'Couleur'
    }
}">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <p class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Options formulaire</p>
        <div class="flex items-center gap-2">
            {{-- Type filter --}}
            <form method="GET" action="{{ route('referentials.form-options') }}" class="flex items-center gap-2">
                <select name="type" onchange="this.form.submit()" class="h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-600 focus:outline-none focus:border-slate-900">
                    <option value="">Tous les types</option>
                    @foreach($types as $t)
                        <option value="{{ $t }}" {{ $currentType === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </form>
            <button @click="addErrors = {}; showAddModal = true" class="inline-flex items-center gap-2 rounded-full bg-slate-900 hover:bg-black text-white text-[13px] px-4 h-10 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Ajouter une option
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-200">
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Type</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Valeur</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Parent</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Ordre</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Statut</th>
                    <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($items as $item)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-5 py-3.5 text-[13px] text-slate-500">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">{{ $item->type }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-[13px] font-medium text-slate-900 border-l border-slate-100">{{ $item->value }}</td>
                    <td class="px-5 py-3.5 text-[13px] text-slate-500 border-l border-slate-100">
                        @if($item->parent_type)
                            {{ $item->parent_type }}: {{ $item->parent_value }}
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-[13px] text-slate-500 border-l border-slate-100">{{ $item->sort_order }}</td>
                    <td class="px-5 py-3.5 text-[13px] border-l border-slate-100">
                        @if($item->is_active)
                            <span class="text-emerald-600 font-medium">Actif</span>
                        @else
                            <span class="text-slate-400">Inactif</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right border-l border-slate-100">
                        <button @click="openEdit({
                            id: '{{ $item->id }}',
                            type: '{{ $item->type }}',
                            value: '{{ addslashes($item->value) }}',
                            parent_type: '{{ $item->parent_type }}',
                            parent_value: '{{ addslashes($item->parent_value ?? '') }}',
                            sort_order: {{ $item->sort_order }},
                            is_active: {{ $item->is_active ? 'true' : 'false' }}
                        })" class="text-[13px] font-medium text-slate-600 hover:text-slate-900 transition-colors">
                            Modifier
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <p class="text-[13px] text-slate-400">Aucune option trouvee</p>
                        <p class="text-[11px] text-slate-300 mt-1">Commencez par ajouter une option</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($items->hasPages())
    <div class="px-5 py-4 border-t border-slate-200">
        {{ $items->links() }}
    </div>
    @endif

    {{-- Add Modal --}}
    <template x-teleport="body">
        <div x-show="showAddModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showAddModal = false" x-cloak>
            <div x-show="showAddModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="bg-white border border-slate-200 w-full max-w-md mx-4 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <p class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Ajouter une option</p>
                </div>
                <form action="{{ route('referentials.form-options.store') }}" method="POST" novalidate @submit="validateAdd($event)">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Type</label>
                            <select name="type" x-ref="add_type"
                                class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 transition"
                                :class="addErrors.type ? 'border-red-400 focus:border-red-400' : 'border-slate-200 focus:border-slate-900'">
                                <option value="">Selectionner un type</option>
                                @foreach($types as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </select>
                            <p x-show="addErrors.type" x-text="addErrors.type" class="mt-1 text-[12px] text-red-500"></p>
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Valeur</label>
                            <input type="text" name="value" x-ref="add_value" placeholder="Ex: Essence, Blanc..."
                                class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 placeholder-slate-300 focus:outline-none focus:ring-0 transition"
                                :class="addErrors.value ? 'border-red-400 focus:border-red-400' : 'border-slate-200 focus:border-slate-900'"
                                @input="delete addErrors.value">
                            <p x-show="addErrors.value" x-text="addErrors.value" class="mt-1 text-[12px] text-red-500"></p>
                            @error('value')
                                <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Type parent</label>
                                <input type="text" name="parent_type" placeholder="Ex: vehicle_type"
                                    class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 placeholder-slate-300 focus:outline-none focus:ring-0 focus:border-slate-900 transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Valeur parent</label>
                                <input type="text" name="parent_value" placeholder="Ex: Auto"
                                    class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 placeholder-slate-300 focus:outline-none focus:ring-0 focus:border-slate-900 transition">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Ordre d'affichage</label>
                            <input type="number" name="sort_order" value="0" min="0"
                                class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 focus:border-slate-900 transition">
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-3">
                        <button type="button" @click="showAddModal = false" class="rounded-full border border-slate-200 bg-white text-slate-600 text-[13px] px-4 h-10 hover:bg-slate-50 transition-colors">Annuler</button>
                        <button type="submit" class="rounded-full bg-slate-900 hover:bg-black text-white text-[13px] px-4 h-10 transition-colors">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- Edit Modal --}}
    <template x-teleport="body">
        <div x-show="showEditModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showEditModal = false" x-cloak>
            <div x-show="showEditModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="bg-white border border-slate-200 w-full max-w-md mx-4 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <p class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Modifier l'option</p>
                </div>
                <form :action="'{{ route('referentials.form-options.update', ':id') }}'.replace(':id', editItem.id)" method="POST" novalidate @submit="validateEdit($event)">
                    @csrf
                    @method('PUT')
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Type</label>
                            <input type="text" disabled :value="editItem.type"
                                class="w-full h-10 px-3 border border-slate-200 bg-slate-50 text-[13px] text-slate-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Valeur</label>
                            <input type="text" name="value" x-model="editItem.value"
                                class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 placeholder-slate-300 focus:outline-none focus:ring-0 transition"
                                :class="editErrors.value ? 'border-red-400 focus:border-red-400' : 'border-slate-200 focus:border-slate-900'"
                                @input="delete editErrors.value">
                            <p x-show="editErrors.value" x-text="editErrors.value" class="mt-1 text-[12px] text-red-500"></p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Type parent</label>
                                <input type="text" name="parent_type" x-model="editItem.parent_type"
                                    class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 placeholder-slate-300 focus:outline-none focus:ring-0 focus:border-slate-900 transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Valeur parent</label>
                                <input type="text" name="parent_value" x-model="editItem.parent_value"
                                    class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 placeholder-slate-300 focus:outline-none focus:ring-0 focus:border-slate-900 transition">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Ordre d'affichage</label>
                            <input type="number" name="sort_order" x-model="editItem.sort_order" min="0"
                                class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 focus:border-slate-900 transition">
                        </div>
                        <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
                            <div>
                                <p class="text-[13px] font-medium text-slate-900">Statut</p>
                                <p class="text-[12px] text-slate-400">Activer ou desactiver cet element</p>
                            </div>
                            <div class="flex items-center">
                                <input type="hidden" name="is_active" value="0">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" x-model="editItem.is_active" class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 peer-checked:bg-slate-900 transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:w-4 after:h-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                    <span class="ml-3 text-[13px] text-slate-600" x-text="editItem.is_active ? 'Actif' : 'Inactif'"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-3">
                        <button type="button" @click="showEditModal = false" class="rounded-full border border-slate-200 bg-white text-slate-600 text-[13px] px-4 h-10 hover:bg-slate-50 transition-colors">Annuler</button>
                        <button type="submit" class="rounded-full bg-slate-900 hover:bg-black text-white text-[13px] px-4 h-10 transition-colors">Mettre a jour</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection
