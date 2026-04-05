<?php

use App\Enums\UserAnimeStatus;
use App\Models\Anime;
use function Livewire\Volt\{state, computed, layout};

layout('layouts.app');

state([
    'selectedList' => null,
    'searchQuery'  => '',
]);

/**
 * All available "lists" — the UserAnimeStatus cases + a virtual "Favorites" list.
 * Each list is represented as an associative array with:
 *   - key:   the value used to query (e.g. 'WATCHING', or 'FAVORITES' for is_favorite)
 *   - label: human-readable name
 *   - icon:  Material Symbol icon name
 *   - color: Tailwind text-color class for accent
 */
$allLists = computed(function () {
    $user = auth()->user();

    $statusLists = collect(UserAnimeStatus::cases())
        // Exclude BLACKLISTED — not a meaningful user-facing "list"
        ->reject(fn (UserAnimeStatus $s) => $s === UserAnimeStatus::Blacklisted)
        ->map(function (UserAnimeStatus $status) use ($user) {
            $count = $user->animes()->wherePivot('status', $status->value)->count();

            // Cover image: last anime added to this list
            $coverAnime = $user->animes()
                ->wherePivot('status', $status->value)
                ->latest('anime_user.updated_at')
                ->first();

            $label = match ($status) {
                UserAnimeStatus::Watching    => 'Watching',
                UserAnimeStatus::Completed   => 'Completed',
                UserAnimeStatus::OnHold      => 'On Hold',
                UserAnimeStatus::Dropped     => 'Dropped',
                UserAnimeStatus::PlanToWatch => 'Plan to Watch',
                default                      => $status->value,
            };

            $icon = match ($status) {
                UserAnimeStatus::Watching    => 'play_circle',
                UserAnimeStatus::Completed   => 'task_alt',
                UserAnimeStatus::OnHold      => 'pause_circle',
                UserAnimeStatus::Dropped     => 'cancel',
                UserAnimeStatus::PlanToWatch => 'bookmark_add',
                default                      => 'list',
            };

            $color = match ($status) {
                UserAnimeStatus::Watching    => 'text-indigo-400',
                UserAnimeStatus::Completed   => 'text-emerald-400',
                UserAnimeStatus::OnHold      => 'text-amber-400',
                UserAnimeStatus::Dropped     => 'text-red-400',
                UserAnimeStatus::PlanToWatch => 'text-sky-400',
                default                      => 'text-slate-400',
            };

            return [
                'key'       => $status->value,
                'label'     => $label,
                'icon'      => $icon,
                'color'     => $color,
                'count'     => $count,
                'cover_url' => $coverAnime?->image_url,
            ];
        })
        ->values();

    // Virtual "Favorites" list at top
    $favCount = $user->animes()->wherePivot('is_favorite', true)->count();
    $favCover = $user->animes()
        ->wherePivot('is_favorite', true)
        ->latest('anime_user.updated_at')
        ->first();

    $favorites = [
        'key'       => 'FAVORITES',
        'label'     => 'Favorites',
        'icon'      => 'favorite',
        'color'     => 'text-rose-400',
        'count'     => $favCount,
        'cover_url' => $favCover?->image_url,
    ];

    return collect([$favorites])->concat($statusLists)->all();
});

/**
 * Filtered list cards for Phase 1 (search by list name).
 */
$filteredLists = computed(function () {
    $lists = $this->allLists;

    if (empty($this->searchQuery)) {
        return $lists;
    }

    $query = mb_strtolower($this->searchQuery);

    return array_values(array_filter(
        $lists,
        fn ($list) => str_contains(mb_strtolower($list['label']), $query)
    ));
});

/**
 * Animes in the currently selected list — for Phase 2.
 * Applies the search query to filter within the selected list (max 50).
 */
$listAnimes = computed(function () {
    if (! $this->selectedList) {
        return collect();
    }

    $user = auth()->user();

    $query = $user->animes();

    if ($this->selectedList === 'FAVORITES') {
        $query->wherePivot('is_favorite', true);
    } else {
        $query->wherePivot('status', $this->selectedList);
    }

    if (! empty($this->searchQuery)) {
        $query->where('title', 'like', '%' . $this->searchQuery . '%');
    }

    return $query->latest('anime_user.updated_at')->limit(50)->get();
});

/**
 * Returns the label for the currently selected list.
 */
$selectedListLabel = computed(function () {
    if (! $this->selectedList) {
        return null;
    }

    foreach ($this->allLists as $list) {
        if ($list['key'] === $this->selectedList) {
            return $list['label'];
        }
    }

    return $this->selectedList;
});

// Actions

$selectList = function (string $key) {
    $this->selectedList = $key;
    $this->searchQuery  = '';
};

$goBack = function () {
    $this->selectedList = null;
    $this->searchQuery  = '';
};


?>

<div class="min-h-screen bg-surface text-on-surface">
    <main class="pt-[110px] pb-20 px-6 sm:px-8 max-w-[1600px] mx-auto">

        {{-- PHASE 1: List Index --}}
        @if(! $this->selectedList)

            {{-- Page Header --}}
            <header class="mb-10 md:mb-14">
                <div class="flex items-center gap-3 mb-3">
                    <span class="material-symbols-outlined text-primary text-4xl">collections_bookmark</span>
                    <h1 class="font-headline text-3xl md:text-4xl font-extrabold tracking-tight text-white">My Lists</h1>
                </div>
                <p class="text-on-surface-variant font-body text-sm md:text-base max-w-xl">
                    Your personal anime collection, organized by status. Pick a list to dive in.
                </p>
            </header>

            {{-- Search Bar (Phase 1) --}}
            <div class="mb-10 max-w-xl">
                <div class="flex items-center gap-4 bg-surface-container-lowest rounded-2xl px-5 py-4 focus-within:ring-2 ring-primary transition-all shadow-inner">
                    <span class="material-symbols-outlined text-primary text-[24px] flex-shrink-0">search</span>
                    <input
                        wire:model.live.debounce.200ms="searchQuery"
                        type="text"
                        id="my-lists-search"
                        placeholder="Filter your lists…"
                        class="bg-transparent border-none focus:ring-0 text-on-surface placeholder:text-outline w-full text-lg font-body outline-none"
                    />
                </div>
            </div>

            {{-- List Cards Grid --}}
            @php $lists = $this->filteredLists; @endphp

            @if(count($lists) > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 md:gap-6">
                    @foreach($lists as $list)
                        <div
                            wire:click="selectList('{{ $list['key'] }}')"
                            id="list-card-{{ Str::slug($list['key']) }}"
                            class="group relative rounded-3xl overflow-hidden cursor-pointer
                                   border border-white/5 hover:border-white/15
                                   shadow-lg hover:shadow-2xl hover:shadow-black/50
                                   transition-all duration-300 hover:-translate-y-1.5
                                   bg-surface-container-low"
                        >
                            {{-- Cover Image --}}
                            <div class="h-40 relative overflow-hidden bg-surface-container-high">
                                @if($list['cover_url'])
                                    <img
                                        src="{{ $list['cover_url'] }}"
                                        alt="{{ $list['label'] }} cover"
                                        class="w-full h-full object-cover object-top scale-110 group-hover:scale-125 transition-transform duration-[600ms]"
                                        onerror="this.style.display='none'"
                                    />
                                @endif
                                {{-- Gradient overlay --}}
                                <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-transparent to-surface-container-low"></div>
                                {{-- Icon badge --}}
                                <div class="flex items-center justify-center absolute top-4 right-4 p-2.5 rounded-full bg-black/40 backdrop-blur-md border border-white/10">
                                    <span class="material-symbols-outlined {{ $list['color'] }} text-2xl">{{ $list['icon'] }}</span>
                                </div>
                            </div>

                            {{-- Card Body --}}
                            <div class="px-5 pb-5 pt-3">
                                <h2 class="font-headline text-lg font-bold text-white mb-1 tracking-tight">
                                    {{ $list['label'] }}
                                </h2>
                                <div class="flex items-center justify-between">
                                    <p class="text-on-surface-variant text-sm font-body">
                                        {{ $list['count'] }} {{ Str::plural('anime', $list['count']) }}
                                    </p>
                                    @if($list['count'] === 0)
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant/50 font-label">Empty</span>
                                    @else
                                        <span class="material-symbols-outlined text-on-surface-variant/40 group-hover:text-on-surface-variant/70 group-hover:translate-x-1 transition-all duration-200 text-[20px]">arrow_forward</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Empty state (no lists match search) --}}
                <div class="py-24 flex flex-col items-center justify-center text-center opacity-70">
                    <span class="material-symbols-outlined text-[64px] text-primary mb-6">search_off</span>
                    <p class="text-white font-headline font-semibold text-2xl">No lists match "{{ $searchQuery }}"</p>
                    <p class="text-sm text-on-surface-variant mt-2">Try a different search term.</p>
                </div>
            @endif

        {{-- PHASE 2: Selected List Detail --}}
        @else

            {{-- Contextual Header --}}
            <header class="mb-8 md:mb-10">
                {{-- Back button --}}
                <button
                    wire:click="goBack"
                    id="my-lists-back-btn"
                    class="flex items-center gap-2 text-on-surface-variant hover:text-white transition-colors mb-5 group font-body text-sm cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[20px] group-hover:-translate-x-0.5 transition-transform duration-150">arrow_back</span>
                    Back to Lists
                </button>

                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <h1 class="font-headline text-3xl md:text-4xl font-extrabold tracking-tight text-white">
                            {{ $this->selectedListLabel }}
                        </h1>
                        <p class="text-on-surface-variant text-sm mt-1 font-body">
                            {{ count($this->listAnimes) }} {{ Str::plural('anime', count($this->listAnimes)) }} found
                        </p>
                    </div>

                    {{-- Search within list --}}
                    <div class="w-full sm:max-w-xs">
                        <div class="flex items-center gap-3 bg-surface-container-lowest rounded-2xl px-4 py-3 focus-within:ring-2 ring-primary transition-all shadow-inner">
                            <span class="material-symbols-outlined text-primary text-[20px] flex-shrink-0">search</span>
                            <input
                                wire:model.live.debounce.300ms="searchQuery"
                                type="text"
                                id="my-lists-detail-search"
                                placeholder="Search in {{ $this->selectedListLabel }}…"
                                class="bg-transparent border-none focus:ring-0 text-on-surface placeholder:text-outline w-full text-sm font-body outline-none"
                            />
                        </div>
                    </div>
                </div>
            </header>

            {{-- Loading Indicator --}}
            <div wire:loading class="mb-4">
                <div class="flex items-center gap-2 text-on-surface-variant text-sm font-body">
                    <span class="material-symbols-outlined text-primary animate-spin text-[18px]">sync</span>
                    Updating…
                </div>
            </div>

            {{-- Anime Grid --}}
            @php $animes = $this->listAnimes; @endphp

            @if(count($animes) > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-x-4 md:gap-x-6 gap-y-8 md:gap-y-10"
                     wire:loading.class="opacity-60">
                    @foreach($animes as $anime)
                        <div
                            wire:click="$dispatch('open-anime-modal', '{{ $anime->id }}')"
                            wire:key="my-list-anime-{{ $anime->id }}"
                            class="group relative cursor-pointer"
                        >
                            <div class="aspect-[2/3] w-full rounded-2xl overflow-hidden bg-surface-container-high
                                        transition-all duration-300 group-hover:-translate-y-2
                                        group-hover:shadow-[0_20px_40px_-15px_rgba(0,0,0,0.7)]
                                        group-active:scale-95
                                        border border-transparent group-hover:border-outline-variant/20">
                                <img
                                    alt="{{ $anime->title }}"
                                    class="w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-110"
                                    src="{{ $anime->image_url }}"
                                    onerror="this.style.background='linear-gradient(135deg,#1c253e,#0c1326)';this.removeAttribute('src')"
                                />
                                {{-- Hover overlay with title --}}
                                <div class="absolute inset-0 bg-gradient-to-t from-surface-container-lowest via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-4 md:p-5">
                                    <span class="text-tertiary text-[9px] md:text-[10px] font-bold tracking-[0.2em] uppercase mb-1 drop-shadow-md">
                                        {{ $anime->type ?? 'TV' }}
                                        @php
                                            $genres = is_string($anime->genres) ? json_decode($anime->genres, true) : $anime->genres;
                                        @endphp
                                        @if(is_array($genres) && count($genres) > 0) • {{ $genres[0] }} @endif
                                    </span>
                                    <h3 class="font-headline text-base md:text-lg font-bold leading-tight text-white drop-shadow-lg">
                                        {{ $anime->title }}
                                    </h3>
                                </div>

                                {{-- Status badge --}}
                                @if($anime->pivot->is_favorite)
                                    <div class="absolute top-2.5 left-2.5 z-10">
                                        <span class="material-symbols-outlined material-filled text-rose-400 text-[18px] drop-shadow-md">favorite</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Details below image --}}
                            <div class="mt-3 md:mt-4 group-hover:opacity-0 transition-opacity duration-200 px-1">
                                <h4 class="font-headline text-sm font-bold text-white truncate" title="{{ $anime->title }}">{{ $anime->title }}</h4>
                                <p class="text-on-surface-variant text-[11px] md:text-xs mt-1 font-medium">
                                    {{ $anime->released_year ?: 'N/A' }}
                                    @if($anime->episodes) • {{ $anime->episodes }} Eps @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Empty state --}}
                <div class="py-24 md:py-32 flex flex-col items-center justify-center text-center opacity-70" wire:loading.remove>
                    @if(! empty($searchQuery))
                        <span class="material-symbols-outlined text-[64px] text-primary mb-6">search_off</span>
                        <p class="text-white font-headline font-semibold text-2xl">No results for "{{ $searchQuery }}"</p>
                        <p class="text-sm text-on-surface-variant mt-2 max-w-md mx-auto">Try a different title or clear your search.</p>
                        <button wire:click="$set('searchQuery', '')" class="mt-8 px-8 py-3 rounded-xl bg-surface-variant hover:bg-surface-container-highest transition-colors text-white font-bold text-sm tracking-wide border border-outline-variant/20 shadow-lg cursor-pointer">
                            Clear Search
                        </button>
                    @else
                        <span class="material-symbols-outlined text-[64px] text-primary mb-6">add_circle</span>
                        <p class="text-white font-headline font-semibold text-2xl">This list is empty</p>
                        <p class="text-sm text-on-surface-variant mt-2 max-w-md mx-auto">Start adding anime to your {{ $this->selectedListLabel }} list from the Directory or Discover pages.</p>
                        <a href="{{ route('directory') }}" wire:navigate class="mt-8 px-8 py-3 rounded-xl bg-primary text-on-primary hover:opacity-90 transition-opacity font-bold text-sm tracking-wide shadow-lg">
                            Browse Directory
                        </a>
                    @endif
                </div>
            @endif

        @endif

    </main>

    <x-footer />
</div>
