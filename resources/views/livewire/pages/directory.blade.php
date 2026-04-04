<?php

use App\Models\Anime;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;
use function Livewire\Volt\{state, computed, uses, layout};

layout('layouts.app');
uses([WithPagination::class]);

state([
    'search' => '',
    'genre'  => 'all',
    'year'   => 'all',
    'type'   => 'all',
    'sort'   => 'a-z'
])->url(); // Maintain URL state so links can be shared

$clearFilters = function () {
    $this->search = '';
    $this->genre = 'all';
    $this->year = 'all';
    $this->type = 'all';
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
        // En SQlite / MySQL json string extraction based on exact match of quotes or LIKE.
        // Assuming genres is JSON string like ["Action","Sci-Fi"]
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
    <!-- Main padding prevents navbar overlap -->
    <main class="pt-[110px] pb-20 px-6 sm:px-8 max-w-[1600px] mx-auto">
        <!-- Header Section -->
        <header class="mb-8 md:mb-12">
            <h1 class="font-headline text-3xl md:text-4xl font-extrabold tracking-tight text-white mb-2">Anime Directory</h1>
            <p class="text-on-surface-variant font-body text-sm md:text-base max-w-2xl">Browse our curated selection of premium animated masterpieces, categorized for the discerning viewer.</p>
        </header>

        <!-- Filtering Utility -->
        <section class="mb-12 md:mb-16 bg-surface-container-low p-5 md:p-6 rounded-2xl grid grid-cols-2 sm:flex sm:flex-wrap items-end gap-4 md:gap-6 shadow-sm border border-outline-variant/10">
            <div class="col-span-2 sm:flex-1 min-w-[200px] w-full">
                <label class="block text-label text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-2">Filter by name</label>
                <div class="relative">
                    <input wire:model.live.debounce.300ms="search" class="w-full bg-surface-container-lowest border-none rounded-xl py-3 px-4 text-on-surface placeholder:text-surface-variant focus:ring-2 focus:ring-primary transition-all font-medium text-sm md:text-base shadow-inner" placeholder="Type title..." type="text"/>
                </div>
            </div>

            <div class="w-full sm:w-auto sm:flex-1 md:flex-none md:w-44">
                <label class="block text-label text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-2">Type</label>
                <select wire:model.live="type" class="w-full bg-surface-container-lowest border-none rounded-xl py-3 px-4 text-on-surface focus:ring-2 focus:ring-primary transition-all cursor-pointer text-sm shadow-inner font-medium">
                    <option value="all">Any Type</option>
                    <option value="TV">TV Series</option>
                    <option value="Movie">Movies</option>
                    <option value="OVA">OVA</option>
                    <option value="Special">Specials</option>
                </select>
            </div>

            <div class="w-full sm:w-auto sm:flex-1 md:flex-none md:w-48">
                <label class="block text-label text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-2">Genre</label>
                <select wire:model.live="genre" class="w-full bg-surface-container-lowest border-none rounded-xl py-3 px-4 text-on-surface focus:ring-2 focus:ring-primary transition-all cursor-pointer text-sm shadow-inner font-medium">
                    <option value="all">All Genres</option>
                    @foreach($this->availableGenres as $g)
                        <option value="{{ $g }}">{{ $g }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-auto sm:flex-1 md:flex-none md:w-36">
                <label class="block text-label text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-2">Year</label>
                <select wire:model.live="year" class="w-full bg-surface-container-lowest border-none rounded-xl py-3 px-4 text-on-surface focus:ring-2 focus:ring-primary transition-all cursor-pointer text-sm shadow-inner font-medium">
                    <option value="all">Any Year</option>
                    @foreach($this->availableYears as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-auto sm:flex-1 md:flex-none md:w-48">
                <label class="block text-label text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-2">Sort</label>
                <select wire:model.live="sort" class="w-full bg-surface-container-lowest border-none rounded-xl py-3 px-4 text-on-surface focus:ring-2 focus:ring-primary transition-all cursor-pointer text-sm shadow-inner font-medium">
                    <option value="a-z">A to Z</option>
                    <option value="z-a">Z to A</option>
                    <option value="latest_added">Latest Added</option>
                </select>
            </div>
        </section>

        <!-- Loading overlay relative to results -->
        <div class="relative">
            <div wire:loading class="absolute inset-0 z-50 bg-surface/50 backdrop-blur-sm rounded-2xl flex items-start justify-center pt-24">
                <div class="bg-surface-container-high px-6 py-4 rounded-full shadow-2xl flex items-center gap-3 border border-outline-variant/10">
                    <span class="material-symbols-outlined text-primary animate-spin">sync</span>
                    <span class="font-bold text-sm text-on-surface tracking-wider uppercase">Loading Directory...</span>
                </div>
            </div>

            <!-- Directory Grid -->
            @if(count($this->animes) > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-x-4 md:gap-x-6 gap-y-8 md:gap-y-10">
                @foreach($this->animes as $anime)
                <div wire:click="$dispatch('open-anime-modal', '{{ $anime->id }}')" class="group relative cursor-pointer" wire:key="anime-{{ $anime->id }}">
                    <div class="aspect-[2/3] w-full rounded-2xl overflow-hidden bg-surface-container-high transition-all duration-300 group-hover:-translate-y-2 group-hover:shadow-[0_20px_40px_-15px_rgba(0,0,0,0.7)] group-active:scale-95 border border-transparent group-hover:border-outline-variant/20">
                        <img
                            alt="{{ $anime->title }}"
                            class="w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-110"
                            src="{{ $anime->image_url }}"
                            onerror="this.style.background='linear-gradient(135deg,#1c253e,#0c1326)';this.removeAttribute('src')"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-surface-container-lowest via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-4 md:p-5">

                            <span class="text-tertiary text-[9px] md:text-[10px] font-bold tracking-[0.2em] uppercase mb-1 drop-shadow-md">
                                {{ $anime->type ?? 'TV' }}
                                @if(is_string($anime->genres))
                                    @php $ags = json_decode($anime->genres, true); @endphp
                                    @if(is_array($ags) && count($ags) > 0) • {{ $ags[0] }} @endif
                                @elseif(is_array($anime->genres) && count($anime->genres) > 0)
                                    • {{ $anime->genres[0] }}
                                @endif
                            </span>

                            <h3 class="font-headline text-base md:text-lg font-bold leading-tight text-white drop-shadow-lg">{{ $anime->title }}</h3>
                        </div>
                    </div>
                    <!-- Details below image -->
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

            <!-- Pagination -->
            <div class="mt-8 md:mt-12 w-full flex justify-center pb-8">
                {{ $this->animes->links('vendor.livewire.directory-pagination') }}
            </div>
            @else
            <!-- No Results State -->
            <div class="py-24 md:py-32 flex flex-col items-center justify-center text-center opacity-70">
                <span class="material-symbols-outlined text-[64px] text-primary mb-6">travel_explore</span>
                <p class="text-white font-headline font-semibold text-2xl">No anime matches your filters</p>
                <p class="text-sm text-on-surface-variant mt-2 max-w-md mx-auto">Try trying a different spelling, adjusting the year, or exploring other categories.</p>
                <button wire:click="clearFilters" class="mt-8 px-8 py-3 rounded-xl bg-surface-variant hover:bg-surface-container-highest transition-colors text-white font-bold text-sm tracking-wide border border-outline-variant/20 shadow-lg cursor-pointer">
                    Clear Filters
                </button>
            </div>
            @endif
        </div>
    </main>

    <x-footer />
</div>
