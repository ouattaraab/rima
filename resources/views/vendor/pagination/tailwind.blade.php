@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-between">

        {{-- Mobile --}}
        <div class="flex flex-1 items-center justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="text-[12px] text-slate-300 cursor-not-allowed">Precedent</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="text-[12px] text-slate-500 hover:text-slate-900 underline transition">Precedent</a>
            @endif

            <span class="text-[12px] text-slate-400">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="text-[12px] text-slate-500 hover:text-slate-900 underline transition">Suivant</a>
            @else
                <span class="text-[12px] text-slate-300 cursor-not-allowed">Suivant</span>
            @endif
        </div>

        {{-- Desktop --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <p class="text-[12px] text-slate-400">
                @if ($paginator->firstItem())
                    <span class="font-medium text-slate-600">{{ $paginator->firstItem() }}</span>
                    a
                    <span class="font-medium text-slate-600">{{ $paginator->lastItem() }}</span>
                    sur
                @else
                    {{ $paginator->count() }} sur
                @endif
                <span class="font-medium text-slate-600">{{ $paginator->total() }}</span>
                resultats
            </p>

            <div class="flex items-center gap-0.5">
                {{-- Previous --}}
                @if ($paginator->onFirstPage())
                    <span class="inline-flex items-center justify-center w-8 h-8 text-slate-300 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center justify-center w-8 h-8 text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition" aria-label="Precedent">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    </a>
                @endif

                {{-- Pages --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex items-center justify-center w-8 h-8 text-[12px] text-slate-300">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="inline-flex items-center justify-center w-8 h-8 text-[12px] font-semibold text-white bg-[#2DB56B]">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="inline-flex items-center justify-center w-8 h-8 text-[12px] text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center justify-center w-8 h-8 text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition" aria-label="Suivant">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </a>
                @else
                    <span class="inline-flex items-center justify-center w-8 h-8 text-slate-300 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
