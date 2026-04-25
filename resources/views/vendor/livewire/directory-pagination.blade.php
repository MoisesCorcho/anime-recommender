@if ($paginator->hasPages())
    <div class="mt-8 sm:mt-12 flex justify-center items-center gap-1.5 sm:gap-3 flex-wrap">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <button disabled aria-disabled="true" class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl border border-outline-variant/5 bg-surface-container-lowest flex items-center justify-center text-outline-variant/50 cursor-not-allowed transition-all">
                <span class="material-symbols-outlined text-[18px] sm:text-[22px]">chevron_left</span>
            </button>
        @else
            <button wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" rel="prev" class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl border border-outline-variant/20 flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high hover:text-white transition-all cursor-pointer shadow-sm hover:shadow-md hover:border-outline-variant/40">
                <span class="material-symbols-outlined text-[18px] sm:text-[22px]">chevron_left</span>
            </button>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="text-on-surface-variant px-1 sm:px-2 text-sm sm:text-base font-bold select-none cursor-default opacity-50">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <button aria-current="page" class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl bg-primary text-on-primary font-extrabold shadow-[0_0_15px_rgba(159,167,255,0.25)] text-sm sm:text-base cursor-default select-none border border-primary/30">
                            {{ $page }}
                        </button>
                    @else
                        <button wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl border border-outline-variant/20 flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high hover:text-white transition-all text-sm sm:text-base font-bold cursor-pointer hover:border-outline-variant/40">
                            {{ $page }}
                        </button>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <button wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" rel="next" class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl border border-outline-variant/20 flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high hover:text-white transition-all cursor-pointer shadow-sm hover:shadow-md hover:border-outline-variant/40">
                <span class="material-symbols-outlined text-[18px] sm:text-[22px]">chevron_right</span>
            </button>
        @else
            <button disabled aria-disabled="true" class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl border border-outline-variant/5 bg-surface-container-lowest flex items-center justify-center text-outline-variant/50 cursor-not-allowed transition-all">
                <span class="material-symbols-outlined text-[18px] sm:text-[22px]">chevron_right</span>
            </button>
        @endif
    </div>
@endif
