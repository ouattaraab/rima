@extends('layouts.app')

@section('title', 'Cat&eacute;gories')
@section('header', 'Cat&eacute;gories')

@section('content')
<div x-data="{
    showAddModal: false,
    showEditModal: false,
    addErrors: {},
    editErrors: {},
    editItem: { id: '', name: '', vehicle_type: '', is_active: true },
    validateAdd(e) {
        this.addErrors = {};
        if (!this.$refs.add_name.value.trim()) this.addErrors.name = 'Le nom est obligatoire.';
        if (Object.keys(this.addErrors).length) { e.preventDefault(); return; }
    },
    validateEdit(e) {
        this.editErrors = {};
        if (!this.editItem.name.trim()) this.editErrors.name = 'Le nom est obligatoire.';
        if (Object.keys(this.editErrors).length) { e.preventDefault(); return; }
    },
    openEdit(item) {
        this.editErrors = {};
        this.editItem = { id: item.id, name: item.name, vehicle_type: item.vehicle_type || '', is_active: item.is_active };
        this.showEditModal = true;
    }
}">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <p class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Cat&eacute;gories</p>
        <div class="flex items-center gap-2">
            <button @click="addErrors = {}; showAddModal = true" class="inline-flex items-center gap-2 rounded-full bg-[#2DB56B] hover:bg-[#2AAE64] text-white text-[13px] px-4 h-10 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Ajouter une cat&eacute;gorie
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-200">
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Nom</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Type v&eacute;hicule</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Statut</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Date cr&eacute;ation</th>
                    <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($items as $item)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-5 py-3.5 text-[13px] font-medium text-slate-900">{{ $item->name }}</td>
                    <td class="px-5 py-3.5 text-[13px] text-slate-500 border-l border-slate-100">{{ $item->vehicle_type ?? '-' }}</td>
                    <td class="px-5 py-3.5 text-[13px] border-l border-slate-100">
                        @if($item->is_active)
                            <span class="text-emerald-600 font-medium">Actif</span>
                        @else
                            <span class="text-slate-400">Inactif</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-[13px] text-slate-500 border-l border-slate-100">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-5 py-3.5 text-right border-l border-slate-100">
                        <button @click="openEdit({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', vehicle_type: '{{ addslashes($item->vehicle_type ?? '') }}', is_active: {{ $item->is_active ? 'true' : 'false' }} })" class="text-[13px] font-medium text-slate-600 hover:text-slate-900 transition-colors">
                            Modifier
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-16 text-center">
                        <p class="text-[13px] text-slate-400">Aucune cat&eacute;gorie trouv&eacute;e</p>
                        <p class="text-[11px] text-slate-300 mt-1">Commencez par ajouter une cat&eacute;gorie</p>
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
                    <p class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Ajouter une cat&eacute;gorie</p>
                </div>
                <form action="{{ route('referentials.vehicle-categories.store') }}" method="POST" novalidate @submit="validateAdd($event)">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div>
                            <label for="add-name" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Nom</label>
                            <input type="text" name="name" id="add-name" x-ref="add_name" value="{{ old('name') }}" placeholder="Nom de la cat&eacute;gorie"
                                class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 placeholder-slate-300 focus:outline-none focus:ring-0 transition"
                                :class="addErrors.name ? 'border-red-400 focus:border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'"
                                @input="delete addErrors.name">
                            <p x-show="addErrors.name" x-text="addErrors.name" class="mt-1 text-[12px] text-red-500"></p>
                            @error('name')
                                <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="add-vehicle-type" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Type de v&eacute;hicule</label>
                            <select name="vehicle_type" id="add-vehicle-type" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 focus:border-[#2DB56B] transition">
                                <option value="">-- Aucun --</option>
                                @foreach($vehicleTypes as $vt)
                                    <option value="{{ $vt->name }}" {{ old('vehicle_type') == $vt->name ? 'selected' : '' }}>{{ $vt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-3">
                        <button type="button" @click="showAddModal = false" class="rounded-full border border-slate-200 bg-white text-slate-600 text-[13px] px-4 h-10 hover:bg-slate-50 transition-colors">Annuler</button>
                        <button type="submit" class="rounded-full bg-[#2DB56B] hover:bg-[#2AAE64] text-white text-[13px] px-4 h-10 transition-colors">Enregistrer</button>
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
                    <p class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Modifier la cat&eacute;gorie</p>
                </div>
                <form :action="'{{ route('referentials.vehicle-categories.update', ':id') }}'.replace(':id', editItem.id)" method="POST" novalidate @submit="validateEdit($event)">
                    @csrf
                    @method('PUT')
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Nom</label>
                            <input type="text" name="name" x-model="editItem.name"
                                class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 placeholder-slate-300 focus:outline-none focus:ring-0 transition"
                                :class="editErrors.name ? 'border-red-400 focus:border-red-400' : 'border-slate-200 focus:border-[#2DB56B]'"
                                @input="delete editErrors.name">
                            <p x-show="editErrors.name" x-text="editErrors.name" class="mt-1 text-[12px] text-red-500"></p>
                            @error('name')
                                <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Type de v&eacute;hicule</label>
                            <select name="vehicle_type" x-model="editItem.vehicle_type" class="w-full h-10 px-3 border border-slate-200 bg-white text-[13px] text-slate-900 focus:outline-none focus:ring-0 focus:border-[#2DB56B] transition">
                                <option value="">-- Aucun --</option>
                                @foreach($vehicleTypes as $vt)
                                    <option value="{{ $vt->name }}">{{ $vt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
                            <div>
                                <p class="text-[13px] font-medium text-slate-900">Statut</p>
                                <p class="text-[12px] text-slate-400">Activer ou d&eacute;sactiver cet &eacute;l&eacute;ment</p>
                            </div>
                            <div class="flex items-center">
                                <input type="hidden" name="is_active" value="0">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" x-model="editItem.is_active" class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 peer-checked:bg-[#2DB56B] transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:w-4 after:h-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                    <span class="ml-3 text-[13px] text-slate-600" x-text="editItem.is_active ? 'Actif' : 'Inactif'"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-3">
                        <button type="button" @click="showEditModal = false" class="rounded-full border border-slate-200 bg-white text-slate-600 text-[13px] px-4 h-10 hover:bg-slate-50 transition-colors">Annuler</button>
                        <button type="submit" class="rounded-full bg-[#2DB56B] hover:bg-[#2AAE64] text-white text-[13px] px-4 h-10 transition-colors">Mettre &agrave; jour</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection
