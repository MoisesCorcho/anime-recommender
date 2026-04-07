<?php

use App\Models\Anime;
use Illuminate\Support\Facades\Session;
use function Livewire\Volt\{state, on, computed};

state([
    'showModal' => false,
    'search' => '',
    'filter' => 'all'
]);

on(['open-search-modal' => function () {
    $this->showModal = true;
    $this->search = '';
    $this->filter = 'all';
}]);

$animes = computed(function () {
    if (empty($this->search)) {
        return [];
    }

    $query = Anime::query()
        ->where('title', 'like', '%' . $this->search . '%');

    if ($this->filter !== 'all') {
        $query->where('type', $this->filter);
    }

    return $query->take(10)->get();
});

$recentSearches = computed(function () {
    return Session::get('recent_searches', []);
});

$setFilter = function ($filterType) {
    if ($this->filter === $filterType) {
        $this->filter = 'all';
    } else {
        $this->filter = $filterType;
    }
};

$selectSearch = function ($term) {
    $this->search = $term;
};

$selectAnime = function ($id, $title) {
    $recent = Session::get('recent_searches', []);
    array_unshift($recent, $title);
    $recent = array_unique($recent);
    $recent = array_slice($recent, 0, 5);
    Session::put('recent_searches', $recent);

    $this->showModal = false;
    $this->dispatch('open-anime-modal', (string)$id);
};

?>

<div
    x-data="{ show: $wire.entangle('showModal') }"
    x-show="show"
    x-effect="document.body.classList.toggle('overflow-hidden', show)"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    x-on:keydown.escape.window="show = false"
    class="fixed inset-0 z-[100] flex items-start justify-center pt-[153px] px-4"
    style="display: none;"
    id="search-modal"
    role="dialog"
    aria-modal="true"
    x-cloak
>
    {{-- Background Blur Overlay --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm pointer-events-auto" @click="show = false"></div>

    {{-- Modal Container --}}
    <div class="relative w-full max-w-2xl bg-surface-container-low border border-outline-variant/20 rounded-3xl shadow-[0_40px_100px_-20px_rgba(0,0,0,0.85)] overflow-hidden z-10 flex flex-col h-auto max-h-[75vh]">

        {{-- Aesthetic glow blob --}}
        <div class="absolute top-0 right-0 w-full h-64 z-0 opacity-20 blur-3xl overflow-hidden pointer-events-none">
            <div class="absolute inset-0 bg-primary/40 rounded-full scale-150 -translate-y-1/2 translate-x-1/2"></div>
        </div>

        {{-- Search Input Area --}}
        <div class="p-6 border-b border-outline-variant/10 shrink-0 relative z-10 flex flex-col md:flex-row items-center gap-4">
            <div class="w-full flex-grow">
                <x-search-input model="search" placeholder="Search for titles, studios, or genres..." debounce="300ms" size="lg" :autofocus="true">
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <kbd class="hidden md:inline-flex items-center justify-center px-2 py-1 text-xs font-semibold text-outline-variant bg-surface-container rounded border border-outline-variant/20 uppercase tracking-widest">Esc</kbd>
                    </div>
                </x-search-input>
            </div>

            {{-- Desktop Close Button --}}
            <button @click="show = false" class="hidden md:flex flex-shrink-0 items-center justify-center p-3 rounded-2xl bg-surface-variant hover:bg-surface-container-highest transition-colors text-on-surface-variant hover:text-white border border-outline-variant/10 cursor-pointer" aria-label="Cerrar Búsqueda" title="Close Search">
                <span class="material-symbols-outlined text-[28px]">close</span>
            </button>
        </div>

        {{-- Quick Filter Chips --}}
        <div class="px-6 py-4 flex flex-wrap md:flex-nowrap gap-2 border-b border-outline-variant/5 overflow-x-auto hide-scrollbar shrink-0 relative z-10">
            @foreach(['all' => 'All Results', 'TV' => 'TV Series', 'Movie' => 'Movies', 'OVA' => 'OVA', 'Special' => 'Specials'] as $value => $chipLabel)
                <button wire:click="setFilter('{{ $value }}')" class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap transition-colors border {{ $filter === $value ? 'bg-primary-container/20 text-primary-fixed border-primary/30 font-bold' : 'bg-surface-variant text-on-surface-variant border-transparent hover:bg-surface-container-highest' }}">
                    {{ strtoupper($chipLabel) }}
                </button>
            @endforeach
        </div>

        {{-- Results Section --}}
        <div class="overflow-y-auto hide-scrollbar flex-grow min-h-0 relative z-10">

            @if(empty($search))
                @if(count($this->recentSearches) > 0)
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xs font-label uppercase tracking-[0.2em] text-on-surface-variant font-bold">Recent Searches</h3>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($this->recentSearches as $recent)
                                <button wire:click="selectSearch('{{ addslashes($recent) }}')" class="flex items-center gap-2 px-3 py-2 bg-surface-container-low hover:bg-surface-container text-on-surface text-sm rounded-lg border border-outline-variant/10 transition-colors">
                                    <span class="material-symbols-outlined text-[16px] text-outline">history</span>
                                    {{ $recent }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @else
                    <x-empty-state icon="manage_search" title="What are you looking for?" description="Search for your next favorite anime by title, studio, or genre." />
                @endif
            @else
                @if(count($this->animes) > 0)
                    <div class="p-4 space-y-1">
                        @foreach($this->animes as $anime)
                            <div
                                wire:click="selectAnime('{{ $anime->id }}', '{{ addslashes($anime->title) }}')"
                                class="group flex items-center gap-4 p-3 rounded-2xl hover:bg-indigo-500/10 hover:border-transparent transition-all cursor-pointer border border-transparent"
                            >
                                <div class="h-16 w-12 rounded-lg overflow-hidden flex-shrink-0 shadow-lg relative bg-surface-container">
                                    <img
                                        src="{{ $anime->image_url }}"
                                        alt="{{ $anime->title }}"
                                        class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                        onerror="this.style.background='linear-gradient(135deg,#1c253e,#0c1326)';this.removeAttribute('src')"
                                    >
                                </div>
                                <div class="flex-grow min-w-0">
                                    <div class="flex items-start md:items-center justify-between flex-col md:flex-row gap-1 md:gap-0">
                                        <h3 class="font-headline font-bold text-on-surface group-hover:text-primary transition-colors truncate pr-4 max-w-full">{{ $anime->title }}</h3>
                                        @if($anime->type)
                                            <div class="flex-shrink-0 hidden sm:block">
                                                <x-badge :label="$anime->type" :variant="strtolower($anime->type) === 'tv' ? 'tertiary' : 'default'" />
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex gap-2 text-sm text-on-surface-variant font-medium mt-1">
                                        @if($anime->score)
                                            <span class="flex items-center gap-1">
                                                <span class="material-symbols-outlined material-filled text-primary/80 text-[14px]">star</span>
                                                {{ number_format((float)$anime->score, 1) }}
                                            </span>
                                        @endif
                                        @if($anime->released_year)
                                            <span class="flex items-center gap-1">• {{ $anime->released_year }}</span>
                                        @endif
                                        @if($anime->type)
                                            <span class="sm:hidden flex items-center gap-1">• {{ $anime->type }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="material-symbols-outlined text-outline-variant opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">arrow_forward_ios</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state icon="search_off" title="No matches found" description="Try adjusting your search term or filters." />
                @endif
            @endif
        </div>

        {{-- Footer Hint --}}
        <div class="p-4 bg-surface-container-lowest/50 flex justify-between items-center text-[10px] font-bold text-outline tracking-widest uppercase px-8 shrink-0">
            <div class="flex gap-4">
                <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined !text-[14px]">touch_app</span>
                    Select
                </span>
            </div>
            <span>Search powered by curator engine</span>
        </div>
    </div>

    {{-- Close Button (Mobile Only - Floating) --}}
    <button @click="show = false" class="absolute top-25 right-4 z-[60] p-2 bg-surface-container-highest/80 backdrop-blur-md rounded-full text-white active:bg-black/70 transition-colors flex md:hidden" aria-label="Cerrar modal">
        <span class="material-symbols-outlined text-[20px]">close</span>
    </button>
</div>
