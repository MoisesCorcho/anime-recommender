<?php

use App\Models\Anime;
use function Livewire\Volt\{layout, with};

layout('layouts.app');

with(fn () => [
    'heroAnimes'        => Anime::inRandomOrder()->take(10)->get(),
    'popularAnimes'     => Anime::orderByDesc('score')->take(4)->get(),
    'recommendedAnimes' => Anime::inRandomOrder()->take(4)->get(),
]);
?>

<div class="min-h-screen pb-24 bg-surface text-on-surface">

    <x-dashboard.hero :animes="$heroAnimes" />

    <div class="max-w-7xl mx-auto px-6 sm:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14">

            <section>
                <x-section-header title="Most Popular" route="#" />

                <div class="grid grid-cols-2 gap-4">
                    @foreach($popularAnimes as $anime)
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
                    @foreach($recommendedAnimes as $anime)
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
