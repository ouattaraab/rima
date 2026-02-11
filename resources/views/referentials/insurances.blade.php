@extends('layouts.app')

@section('title', 'Assurances')
@section('header', 'Assurances')

@section('content')
<div x-data="{
    showAddModal: false,
    showEditModal: false,
    showImportModal: false,
    addErrors: {},
    editErrors: {},
    editItem: { id: '', name: '', is_active: true },
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
        this.editItem = { id: item.id, name: item.name, is_active: item.is_active };
        this.showEditModal = true;
    }
}">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <p class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Compagnies d'assurance</p>
        <div class="flex items-center gap-2">
            <a href="{{ route('referentials.insurances.export') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white text-slate-600 text-[13px] px-4 h-10 hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Exporter
            </a>
            <button @click="showImportModal = true" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white text-slate-600 text-[13px] px-4 h-10 hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                Importer
            </button>
            <button @click="addErrors = {}; showAddModal = true" class="inline-flex items-center gap-2 rounded-full bg-slate-900 hover:bg-black text-white text-[13px] px-4 h-10 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Ajouter une compagnie
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-200">
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide">Nom</th>
                    <th class="text-left px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Statut</th>
                    <th class="text-right px-5 py-3 text-[11px] font-medium text-slate-400 uppercase tracking-wide border-l border-slate-200">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($items as $item)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-5 py-3.5 text-[13px] font-medium text-slate-900">{{ $item->name }}</td>
                    <td class="px-5 py-3.5 text-[13px] border-l border-slate-100">
                        @if($item->is_active)
                            <span class="text-emerald-600 font-medium">Actif</span>
                        @else
                            <span class="text-slate-400">Inactif</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right border-l border-slate-100">
                        <button @click="openEdit({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', is_active: {{ $item->is_active ? 'true' : 'false' }} })" class="text-[13px] font-medium text-slate-600 hover:text-slate-900 transition-colors">
                            Modifier
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-5 py-16 text-center">
                        <p class="text-[13px] text-slate-400">Aucune compagnie trouvee</p>
                        <p class="text-[11px] text-slate-300 mt-1">Commencez par ajouter une compagnie</p>
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
                    <p class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Ajouter une compagnie</p>
                </div>
                <form action="{{ route('referentials.insurances.store') }}" method="POST" novalidate @submit="validateAdd($event)">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div>
                            <label for="add-name" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Nom</label>
                            <input type="text" name="name" id="add-name" x-ref="add_name" value="{{ old('name') }}" placeholder="Nom de la compagnie"
                                class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 placeholder-slate-300 focus:outline-none focus:ring-0 transition"
                                :class="addErrors.name ? 'border-red-400 focus:border-red-400' : 'border-slate-200 focus:border-slate-900'"
                                @input="delete addErrors.name">
                            <p x-show="addErrors.name" x-text="addErrors.name" class="mt-1 text-[12px] text-red-500"></p>
                            @error('name')
                                <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                            @enderror
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
                    <p class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Modifier la compagnie</p>
                </div>
                <form :action="'{{ route('referentials.insurances.update', ':id') }}'.replace(':id', editItem.id)" method="POST" novalidate @submit="validateEdit($event)">
                    @csrf
                    @method('PUT')
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Nom</label>
                            <input type="text" name="name" x-model="editItem.name"
                                class="w-full h-10 px-3 border bg-white text-[13px] text-slate-900 placeholder-slate-300 focus:outline-none focus:ring-0 transition"
                                :class="editErrors.name ? 'border-red-400 focus:border-red-400' : 'border-slate-200 focus:border-slate-900'"
                                @input="delete editErrors.name">
                            <p x-show="editErrors.name" x-text="editErrors.name" class="mt-1 text-[12px] text-red-500"></p>
                            @error('name')
                                <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                            @enderror
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

    {{-- Import Modal --}}
    <template x-teleport="body">
        <div x-show="showImportModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showImportModal = false" x-cloak>
            <div x-show="showImportModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="bg-white border border-slate-200 w-full max-w-md mx-4 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <p class="text-[13px] font-semibold uppercase tracking-wide text-slate-900">Importer des compagnies d'assurance</p>
                </div>
                <form action="{{ route('referentials.insurances.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="p-6 space-y-4">
                        <p class="text-[13px] text-slate-500">Selectionnez un fichier Excel (.xlsx, .xls) ou CSV contenant une colonne <strong>Nom</strong>.</p>
                        <div>
                            <label for="import-file" class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1.5">Fichier</label>
                            <input type="file" name="file" id="import-file" accept=".xlsx,.xls,.csv" required
                                class="w-full text-[13px] text-slate-900 file:mr-3 file:py-2 file:px-4 file:border-0 file:text-[13px] file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 file:cursor-pointer file:rounded-full">
                            @error('file')
                                <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-3">
                        <button type="button" @click="showImportModal = false" class="rounded-full border border-slate-200 bg-white text-slate-600 text-[13px] px-4 h-10 hover:bg-slate-50 transition-colors">Annuler</button>
                        <button type="submit" class="rounded-full bg-slate-900 hover:bg-black text-white text-[13px] px-4 h-10 transition-colors">Importer</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection
