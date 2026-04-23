<?php

use App\Enums\InteractionType;
use App\Jobs\LogUserInteractionJob;
use App\Models\Anime;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;
use function Livewire\Volt\{state, computed, uses, layout, updated};

layout('layouts.app');
uses([WithPagination::class]);

state([
    'search' => '',
    'genre'  => 'all',
    'year'   => 'all',
    'type'   => 'all',
    'sort'   => 'a-z'
])->url(); // Maintain URL state so links can be shared

$applyFilters = function () {
    if (! Auth::check()) return;

    $filters = array_filter([
        'search' => !empty($this->search) ? $this->search : null,
        'genre'  => $this->genre !== 'all' ? $this->genre : null,
        'year'   => $this->year !== 'all' ? $this->year : null,
        'type'   => $this->type !== 'all' ? $this->type : null,
        'sort'   => $this->sort !== 'a-z' ? $this->sort : null,
    ]);

    if (empty($filters)) return;

    LogUserInteractionJob::dispatch(
        user: Auth::user(),
        type: InteractionType::CatalogFilter,
        payload: InteractionType::catalogFilterPayload(
            filters: $filters,
            resultsCount: $this->animes->total()
        )
    );
};

$clearFilters = function () {
    $this->search = '';
    $this->genre = 'all';
    $this->year = 'all';
    $this->type = 'all';
    $this->sort = 'a-z';
};

$availableYears = computed(function () {
    return Cache::remember('directory_anime_years', 86400, function () {
        return Anime::select('released_year')
            ->distinct()
            ->whereNotNull('released_year')
            ->orderByDesc('released_year')
            ->pluck('released_year')
            ->toArray();
    });
});

$availableGenres = computed(function () {
    return Cache::remember('directory_anime_genres', 86400, function () {
        $allGenres = [];
        Anime::select('genres')->whereNotNull('genres')->chunk(1000, function ($animes) use (&$allGenres) {
            foreach ($animes as $anime) {
                if (is_array($anime->genres)) {
                    foreach ($anime->genres as $g) {
                        $allGenres[$g] = true;
                    }
                } elseif (is_string($anime->genres)) {
                    $decoded = json_decode($anime->genres, true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $g) {
                            $allGenres[$g] = true;
                        }
                    }
                }
            }
        });
        $unique = array_keys($allGenres);
        sort($unique);
        return $unique;
    });
});

$animes = computed(function () {
    $query = Anime::query();

    if (!empty($this->search)) {
        $query->where('title', 'like', '%' . $this->search . '%');
    }

    if ($this->genre !== 'all') {
        $query->where('genres', 'like', '%"' . $this->genre . '"%');
    }

    if ($this->year !== 'all') {
        $query->where('released_year', $this->year);
    }

    if ($this->type !== 'all') {
        $query->where('type', $this->type);
    }

    if ($this->sort === 'latest_added') {
        $query->latest('id');
    } elseif ($this->sort === 'a-z') {
        $query->orderBy('title', 'asc');
    } elseif ($this->sort === 'z-a') {
        $query->orderBy('title', 'desc');
    }

    return $query->paginate(24);
});

?>

<div class="min-h-screen bg-surface text-on-surface">
    <main class="pt-[110px] pb-20 px-6 sm:px-8 max-w-[1600px] mx-auto">

        <x-page-header
            title="Anime Directory"
            description="Browse our curated selection of premium animated masterpieces, categorized for the discerning viewer."
        />

        {{-- Filtering Utility --}}
        <section class="mb-12 md:mb-16 bg-surface-container-low p-5 md:p-6 rounded-2xl grid grid-cols-2 sm:flex sm:flex-wrap items-end gap-4 md:gap-6 shadow-sm border border-outline-variant/10">

            {{-- Text search --}}
            <div class="col-span-2 sm:flex-1 min-w-[200px] w-full">
                <label class="block text-label text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-2">Filter by name</label>
                <x-search-input model="search" placeholder="Type title..." size="sm" :defer="true" />
            </div>

            {{-- Type --}}
            <div class="w-full sm:w-auto sm:flex-1 md:flex-none md:w-44">
                <x-select-filter
                    label="Type"
                    model="type"
                    placeholder="Any Type"
                    :options="['TV' => 'TV Series', 'Movie' => 'Movies', 'OVA' => 'OVA', 'Special' => 'Specials']"
                    :defer="true"
                />
            </div>

            {{-- Genre --}}
            <div class="w-full sm:w-auto sm:flex-1 md:flex-none md:w-48">
                <x-select-filter label="Genre" model="genre" placeholder="All Genres" :defer="true">
                    @foreach($this->availableGenres as $g)
                        <option value="{{ $g }}">{{ $g }}</option>
                    @endforeach
                </x-select-filter>
            </div>

            {{-- Year --}}
            <div class="w-full sm:w-auto sm:flex-1 md:flex-none md:w-36">
                <x-select-filter label="Year" model="year" placeholder="Any Year" :defer="true">
                    @foreach($this->availableYears as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </x-select-filter>
            </div>

            {{-- Sort --}}
            <div class="w-full sm:w-auto sm:flex-1 md:flex-none md:w-48">
                <x-select-filter
                    label="Sort"
                    model="sort"
                    placeholder=""
                    :options="['a-z' => 'A to Z', 'z-a' => 'Z to A', 'latest_added' => 'Latest Added']"
                    :defer="true"
                />
            </div>

            {{-- Filter Button --}}
            <div class="col-span-2 sm:w-full md:w-auto">
                <button
                    wire:click="applyFilters"
                    class="w-full md:w-auto px-8 py-3 rounded-2xl bg-primary hover:bg-primary/90 transition-all text-on-primary font-bold text-sm tracking-wide shadow-lg shadow-primary/20 flex items-center justify-center gap-2 cursor-pointer active:scale-95"
                >
                    <span class="material-symbols-outlined text-[20px]">filter_alt</span>
                    Filter
                </button>
            </div>
        </section>

        {{-- Results area --}}
        <div class="relative">
            <x-loading-overlay message="Loading Directory..." />

            @if(count($this->animes) > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-x-4 md:gap-x-6 gap-y-8 md:gap-y-10">
                    @foreach($this->animes as $anime)
                        <x-anime-grid-card :anime="$anime" :wireKey="'dir-anime-'.$anime->id" />
                    @endforeach
                </div>

                <div class="mt-8 md:mt-12 w-full flex justify-center pb-8">
                    {{ $this->animes->links('vendor.livewire.directory-pagination') }}
                </div>
            @else
                <x-empty-state
                    icon="travel_explore"
                    title="No anime matches your filters"
                    description="Try a different spelling, adjusting the year, or exploring other categories."
                >
                    <button wire:click="clearFilters" class="mt-8 px-8 py-3 rounded-xl bg-surface-variant hover:bg-surface-container-highest transition-colors text-white font-bold text-sm tracking-wide border border-outline-variant/20 shadow-lg cursor-pointer">
                        Clear Filters
                    </button>
                </x-empty-state>
            @endif
        </div>

    </main>

    <x-footer />
</div>
