@props(['structures', 'selected' => []])

@php
    $structuresJson = $structures->map(fn($s) => [
        'c' => $s->code,
        'l' => $s->code . ' - ' . ($s->sigle ?? $s->name),
        's' => strtolower($s->code . ' ' . ($s->sigle ?? '') . ' ' . $s->name),
    ])->values()->toJson();
@endphp

<div x-data="{
    open: false,
    selected: {{ json_encode(is_array($selected) ? $selected : []) }},
    search: '',
    items: {{ $structuresJson }},
    get filtered() {
        if (!this.search) return this.items;
        const q = this.search.toLowerCase();
        return this.items.filter(s => s.s.includes(q));
    },
    toggle(code) {
        const i = this.selected.indexOf(code);
        if (i > -1) this.selected.splice(i, 1);
        else this.selected.push(code);
    },
    has(code) { return this.selected.includes(code); },
    clear() { this.selected = []; },
    close() { this.open = false; this.search = ''; }
}" class="relative flex-1 min-w-0" @keydown.escape.window="close()">

    {{-- Hidden inputs --}}
    <template x-for="c in selected" :key="c">
        <input type="hidden" name="structures[]" :value="c">
    </template>

    {{-- Trigger --}}
    <div class="relative">
        <input type="text" x-model="search"
               @focus="open = true" @click="open = true"
               :placeholder="selected.length ? selected.length + ' structure(s)' : 'Structure : Toutes'"
               class="filter-input w-full pr-8" autocomplete="off">
        <button type="button" x-show="selected.length > 0" @click.stop="clear()"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                title="Effacer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Dropdown --}}
    <div x-show="open" @click.away="close()" x-transition.opacity.duration.150ms
         class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-xl overflow-hidden"
         x-cloak>

        {{-- Scrollable list — fixed height, native scroll --}}
        <div x-ref="listbox"
             style="max-height: 240px; overflow-y: scroll; overscroll-behavior: contain;"
             x-on:wheel.stop>
            <template x-for="item in filtered" :key="item.c">
                <label class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-50 cursor-pointer text-[13px] text-slate-700"
                       :class="{ 'bg-emerald-50/60': has(item.c) }">
                    <input type="checkbox" :checked="has(item.c)" @change="toggle(item.c)"
                           class="w-3.5 h-3.5 rounded border-slate-300 text-[#2DB56B] focus:ring-0 focus:ring-offset-0 cursor-pointer shrink-0">
                    <span x-text="item.l" class="truncate"></span>
                </label>
            </template>
            <div x-show="filtered.length === 0" class="px-3 py-6 text-center text-[12px] text-slate-400">
                Aucun resultat
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between px-3 py-1.5 border-t border-slate-100 bg-slate-50 text-[11px]">
            <span class="text-slate-400">
                <span x-text="filtered.length"></span> / <span x-text="items.length"></span>
                <span x-show="selected.length" class="text-emerald-600 font-semibold ml-1">&bull; <span x-text="selected.length"></span> sel.</span>
            </span>
            <button type="button" x-show="selected.length > 0" @click.stop="clear()"
                    class="text-slate-500 hover:text-slate-900 underline">Effacer</button>
        </div>
    </div>
</div>
