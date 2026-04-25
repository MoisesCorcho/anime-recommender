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
 */
$allLists = computed(function () {
    $user = auth()->user();

    $statusLists = collect(UserAnimeStatus::cases())
        ->reject(fn (UserAnimeStatus $s) => $s === UserAnimeStatus::Blacklisted)
        ->map(function (UserAnimeStatus $status) use ($user) {
            $count = $user->animes()->wherePivot('status', $status->value)->count();

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

$listAnimes = computed(function () {
    if (! $this->selectedList) {
        return collect();
    }

    $user  = auth()->user();
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

            <x-page-header
                title="My Lists"
                icon="collections_bookmark"
                description="Your personal anime collection, organized by status. Pick a list to dive in."
            />

            {{-- Search bar (Phase 1) --}}
            <div class="mb-10 max-w-xl">
                <x-search-input
                    model="searchQuery"
                    placeholder="Filter your lists…"
                    debounce="200ms"
                    id="my-lists-search"
                    size="lg"
                />
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
                                <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-transparent to-surface-container-low"></div>
                                <div class="flex items-center justify-center absolute top-4 right-4 p-2.5 rounded-full bg-black/40 backdrop-blur-md border border-white/10">
                                    <span class="material-symbols-outlined {{ $list['color'] }} text-2xl">{{ $list['icon'] }}</span>
                                </div>
                            </div>

                            {{-- Card Body --}}
                            <div class="px-5 pb-5 pt-3">
                                <h2 class="font-headline text-lg font-bold text-white mb-1 tracking-tight">{{ $list['label'] }}</h2>
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
                <x-empty-state
                    icon="search_off"
                    title='No lists match "{{ $searchQuery }}"'
                    description="Try a different search term."
                />
            @endif

        {{-- PHASE 2: Selected List Detail --}}
        @else

            <x-page-header title="{{ $this->selectedListLabel }}">
                <x-slot:before>
                    <button
                        wire:click="goBack"
                        id="my-lists-back-btn"
                        class="flex items-center gap-2 text-on-surface-variant hover:text-white transition-colors mb-5 group font-body text-sm cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[20px] group-hover:-translate-x-0.5 transition-transform duration-150">arrow_back</span>
                        Back to Lists
                    </button>
                </x-slot:before>

                <x-slot:after>
                    <p class="text-on-surface-variant text-sm mt-1 font-body">
                        {{ count($this->listAnimes) }} {{ Str::plural('anime', count($this->listAnimes)) }} found
                    </p>
                    <div class="mt-4 w-full sm:max-w-xs">
                        <x-search-input
                            model="searchQuery"
                            placeholder="Search in {{ $this->selectedListLabel }}…"
                            debounce="300ms"
                            id="my-lists-detail-search"
                            size="sm"
                        />
                    </div>
                </x-slot:after>
            </x-page-header>

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
                        <x-anime-grid-card
                            :anime="$anime"
                            :showFavoriteBadge="$anime->pivot->is_favorite"
                            :wireKey="'my-list-anime-'.$anime->id"
                        />
                    @endforeach
                </div>
            @else
                @if(! empty($searchQuery))
                    <x-empty-state
                        icon="search_off"
                        title='No results for "{{ $searchQuery }}"'
                        description="Try a different title or clear your search."
                    >
                        <button wire:click="$set('searchQuery', '')" class="mt-8 px-8 py-3 rounded-xl bg-surface-variant hover:bg-surface-container-highest transition-colors text-white font-bold text-sm tracking-wide border border-outline-variant/20 shadow-lg cursor-pointer">
                            Clear Search
                        </button>
                    </x-empty-state>
                @else
                    <x-empty-state
                        icon="add_circle"
                        title="This list is empty"
                        description="Start adding anime to your {{ $this->selectedListLabel }} list from the Directory or Discover pages."
                    >
                        <a href="{{ route('directory') }}" wire:navigate class="mt-8 px-8 py-3 rounded-xl bg-primary text-on-primary hover:opacity-90 transition-opacity font-bold text-sm tracking-wide shadow-lg">
                            Browse Directory
                        </a>
                    </x-empty-state>
                @endif
            @endif

        @endif

    </main>

    <x-footer />
</div>
