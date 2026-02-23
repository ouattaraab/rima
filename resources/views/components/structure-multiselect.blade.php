@props(['structures', 'selected' => []])

@php
    $structuresJson = $structures->map(fn($s) => [
        'code' => $s->code,
        'label' => $s->code . ' - ' . ($s->sigle ?? $s->name),
        'search' => strtolower($s->code . ' ' . ($s->sigle ?? '') . ' ' . $s->name),
    ])->values()->toJson();
@endphp

<div x-data="{
    open: false,
    selected: {{ json_encode(is_array($selected) ? $selected : []) }},
    search: '',
    page: 1,
    perPage: 50,
    structures: {{ $structuresJson }},
    get filtered() {
        if (!this.search) return this.structures;
        const q = this.search.toLowerCase();
        return this.structures.filter(s => s.search.includes(q));
    },
    get paginated() {
        return this.filtered.slice(0, this.page * this.perPage);
    },
    get hasMore() {
        return this.filtered.length > this.page * this.perPage;
    },
    get remaining() {
        return this.filtered.length - (this.page * this.perPage);
    },
    toggle(code) {
        const idx = this.selected.indexOf(code);
        if (idx > -1) {
            this.selected.splice(idx, 1);
        } else {
            this.selected.push(code);
        }
    },
    isSelected(code) {
        return this.selected.includes(code);
    },
    loadMore() {
        this.page++;
    },
    clearAll() {
        this.selected = [];
    },
    resetPagination() {
        this.page = 1;
    }
}" class="relative flex-1 min-w-0" @keydown.escape.window="open = false; search = ''">

    {{-- Hidden inputs for form submission --}}
    <template x-for="code in selected" :key="code">
        <input type="hidden" name="structures[]" :value="code">
    </template>

    {{-- Trigger input --}}
    <input type="text"
           x-model="search"
           @focus="open = true"
           @click="open = true"
           @input="resetPagination()"
           :placeholder="selected.length ? selected.length + ' structure(s) selectionnee(s)' : 'Structure : Toutes'"
           class="filter-input w-full"
           autocomplete="off">

    {{-- Dropdown panel --}}
    <div x-show="open"
         @click.away="open = false; search = ''"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg overflow-hidden"
         x-cloak>

        {{-- Scrollable list --}}
        <div class="overflow-y-auto max-h-60 p-1"
             style="overscroll-behavior: contain;"
             @wheel.stop>

            <template x-for="item in paginated" :key="item.code">
                <label class="flex items-center gap-2 px-2.5 py-1.5 hover:bg-slate-50 rounded cursor-pointer text-[13px] text-slate-700 transition-colors">
                    <input type="checkbox"
                           :value="item.code"
                           :checked="isSelected(item.code)"
                           @change="toggle(item.code)"
                           class="w-3.5 h-3.5 rounded border-slate-300 text-[#2DB56B] focus:ring-0 focus:ring-offset-0 cursor-pointer">
                    <span x-text="item.label" class="truncate"></span>
                </label>
            </template>

            {{-- Empty state --}}
            <div x-show="filtered.length === 0" class="px-3 py-4 text-center text-[12px] text-slate-400">
                Aucune structure trouvee
            </div>
        </div>

        {{-- Load more button --}}
        <div x-show="hasMore" class="px-2 py-1.5 border-t border-slate-100">
            <button type="button"
                    @click.stop="loadMore()"
                    class="w-full text-center text-[12px] text-[#2DB56B] hover:text-[#249957] font-medium py-1 hover:bg-slate-50 rounded transition-colors">
                Afficher plus (<span x-text="remaining"></span> restant(s))
            </button>
        </div>

        {{-- Footer with counter and reset --}}
        <div class="flex items-center justify-between px-3 py-2 border-t border-slate-100 bg-slate-50/50">
            <span class="text-[11px] text-slate-400">
                <span x-text="filtered.length"></span> structure(s)
                <span x-show="selected.length > 0" class="text-slate-600 font-medium">
                    &middot; <span x-text="selected.length"></span> selectionnee(s)
                </span>
            </span>
            <button type="button"
                    x-show="selected.length > 0"
                    @click.stop="clearAll()"
                    class="text-[11px] text-slate-500 hover:text-slate-900 underline transition-colors">
                Tout effacer
            </button>
        </div>
    </div>
</div>
