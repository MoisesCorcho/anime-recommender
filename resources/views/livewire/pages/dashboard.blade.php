<?php

use App\Models\Anime;
use Illuminate\Support\Facades\Session;
use function Livewire\Volt\{layout, state, computed, on};

layout('layouts.app');

state(['showNsfw' => fn () => Session::get('show_nsfw', false)]);

on(['nsfw-toggled' => function (bool $value) {
    $this->showNsfw = $value;
    Session::put('show_nsfw', $value);
}]);

$heroAnimes = computed(function () {
    return Anime::query()
        ->when(! $this->showNsfw, fn ($q) => $q
            ->where('type', '!=', 'Hentai')
            ->where(fn ($q2) => $q2
                ->whereNull('genres')
                ->orWhere('genres', 'NOT LIKE', '%"Hentai"%')
            )
        )
        ->inRandomOrder()
        ->take(10)
        ->get();
});

$popularAnimes = computed(function () {
    return Anime::query()
        ->when(! $this->showNsfw, fn ($q) => $q
            ->where('type', '!=', 'Hentai')
            ->where(fn ($q2) => $q2
                ->whereNull('genres')
                ->orWhere('genres', 'NOT LIKE', '%"Hentai"%')
            )
        )
        ->orderByDesc('score')
        ->take(4)
        ->get();
});

$recommendedAnimes = computed(function () {
    return Anime::query()
        ->when(! $this->showNsfw, fn ($q) => $q
            ->where('type', '!=', 'Hentai')
            ->where(fn ($q2) => $q2
                ->whereNull('genres')
                ->orWhere('genres', 'NOT LIKE', '%"Hentai"%')
            )
        )
        ->inRandomOrder()
        ->take(4)
        ->get();
});
?>

<div class="min-h-screen pb-24 bg-surface text-on-surface">

    <x-dashboard.hero :animes="$this->heroAnimes" />

    <div class="max-w-7xl mx-auto px-6 sm:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14">

            <section>
                <x-section-header title="Most Popular" route="#" />

                <div class="grid grid-cols-2 gap-4">
                    @foreach($this->popularAnimes as $anime)
                        <x-anime-card
                            :title="$anime->title"
                            :score="$anime->score"
                            :image="$anime->image_url"
                            :animeId="$anime->id"
                        />
                    @endforeach
                </div>
            </section>

            <section>
                <x-section-header title="Recommended for You" route="#" />

                <div class="grid grid-cols-2 gap-4">
                    @foreach($this->recommendedAnimes as $anime)
                        <x-anime-card
                            :title="$anime->title"
                            :score="$anime->score"
                            :image="$anime->image_url"
                            :animeId="$anime->id"
                        />
                    @endforeach
                </div>
            </section>

        </div>
    </div>

    <x-footer />

</div>
