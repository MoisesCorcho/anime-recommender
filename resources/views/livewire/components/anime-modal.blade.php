<?php

use App\Models\Anime;
use function Livewire\Volt\{state, on};

state(['showModal' => false, 'anime' => null]);

on(['open-anime-modal' => function (string $id) {
    $this->anime     = Anime::find($id);
    $this->showModal = true;
}]);

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
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4 md:p-8"
    id="anime-detail-modal"
    role="dialog"
    aria-modal="true"
>
    {{-- Glass Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-md" @click="show = false"></div>

    {{-- Modal Panel --}}
    <div class="relative w-full max-w-4xl
                max-h-[90vh] md:max-h-none
                overflow-y-auto hide-scrollbar
                md:overflow-hidden
                bg-surface-container-low rounded-2xl
                shadow-[0_40px_100px_-20px_rgba(0,0,0,0.85)]
                flex flex-col md:block
                border border-outline-variant/10">

        {{-- Close Button (desktop only) --}}
        <button
            @click="show = false"
            class="hidden md:flex absolute top-5 right-5 z-50 p-2 bg-surface-container-highest/60 backdrop-blur-sm rounded-full text-on-surface hover:bg-surface-container-highest transition-colors cursor-pointer"
            aria-label="Cerrar modal"
            id="anime-modal-close-btn"
        >
            <span class="material-symbols-outlined text-[22px]">close</span>
        </button>

        @if($anime)

            {{-- Poster Column --}}
            <div class="w-full flex-shrink-0 relative aspect-[2/3] md:w-[45%]">
                <img
                    src="{{ $anime->image_url }}"
                    alt="{{ $anime->title }}"
                    class="absolute inset-0 w-full h-full object-cover object-top"
                    onerror="this.style.background='linear-gradient(135deg,#1c253e,#0c1326)';this.removeAttribute('src')"
                >
                {{-- Right-edge fade into modal background (desktop only) --}}
                <div class="absolute inset-0 md:bg-gradient-to-r md:from-transparent md:to-surface-container-low z-10 pointer-events-none"></div>

                {{-- Mobile close button --}}
                <button
                    @click="show = false"
                    class="flex items-center justify-center md:hidden absolute top-4 right-4 z-30 p-2 bg-black/50 backdrop-blur-md rounded-full text-white active:bg-black/70 transition-colors"
                    aria-label="Cerrar modal"
                    id="anime-modal-close-btn-mobile"
                >
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            {{-- Content Column --}}
            <div class="w-full p-8 flex flex-col relative z-20
                        md:absolute md:top-0 md:right-0 md:bottom-0 md:w-[55%]
                        md:p-8 md:overflow-y-auto md:hide-scrollbar">

                {{-- Aesthetic glow blob --}}
                <div class="absolute top-0 right-0 w-full h-64 -z-10 opacity-20 blur-3xl overflow-hidden pointer-events-none">
                    <div class="absolute inset-0 bg-primary/40 rounded-full scale-150 -translate-y-1/2 translate-x-1/2"></div>
                </div>

                <div class="space-y-5 relative z-10">

                    {{-- Badges row --}}
                    <div class="flex flex-wrap gap-2">
                        <x-badge :label="$anime->type ?? 'TV Series'" variant="default" />

                        @if($anime->score && $anime->score >= 8.5)
                            <x-badge label="Top Rated" variant="tertiary" />
                        @endif
                    </div>

                    {{-- Title --}}
                    <h2 class="text-3xl md:text-4xl font-headline font-extrabold text-white leading-tight tracking-tight">
                        {{ $anime->title }}
                    </h2>

                    {{-- Quick stats --}}
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-on-surface-variant text-sm font-label">
                        @if($anime->score)
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined material-filled text-primary text-[18px]">star</span>
                                {{ number_format((float)$anime->score, 1) }} Rating
                            </span>
                        @endif
                        @if($anime->episodes)
                            <span>{{ $anime->episodes }} Episodes</span>
                        @endif
                        @if($anime->released_year)
                            <span>{{ $anime->released_year }}</span>
                        @endif
                        @if($anime->status)
                            <span class="capitalize">{{ $anime->status }}</span>
                        @endif
                    </div>

                    {{-- Synopsis --}}
                    @if($anime->description)
                        <div class="space-y-3">
                            <h3 class="text-xs font-label uppercase tracking-[0.2em] text-on-surface-variant font-bold">Synopsis</h3>
                            <p class="text-on-surface-variant leading-relaxed text-sm max-w-prose">{{ $anime->description }}</p>
                        </div>
                    @endif

                    {{-- Genre Tags --}}
                    @if($anime->genres && count($anime->genres))
                        <div class="flex flex-wrap gap-2">
                            @foreach($anime->genres as $genre)
                                <span class="px-4 py-1.5 rounded-md bg-surface-container-highest text-on-surface text-xs font-medium border border-outline-variant/10">
                                    {{ $genre }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    {{-- User Actions --}}
                    <div class="pt-4 flex flex-wrap items-center gap-4">
                        <button
                            class="bg-gradient-to-br from-primary to-primary-container text-on-primary px-8 py-3.5 rounded-full font-bold text-sm shadow-lg shadow-primary/20 hover:scale-105 transition-transform flex items-center gap-2"
                            id="anime-modal-watch-btn"
                        >
                            <span class="material-symbols-outlined material-filled text-[20px]">play_circle</span>
                            Where To Watch
                        </button>

                        <div class="flex items-center gap-3">
                            <button class="flex items-center justify-center p-3.5 rounded-full border border-outline-variant/20 hover:bg-surface-container-highest text-on-surface transition-all" aria-label="Añadir a favoritos" id="anime-modal-favorite-btn">
                                <span class="material-symbols-outlined text-[22px]">favorite</span>
                            </button>
                            <button class="flex items-center justify-center p-3.5 rounded-full border border-outline-variant/20 hover:bg-error/10 hover:text-error hover:border-error/30 text-on-surface transition-all" aria-label="Bloquear" id="anime-modal-block-btn">
                                <span class="material-symbols-outlined text-[22px]">block</span>
                            </button>
                            <button class="flex items-center justify-center p-3.5 rounded-full border border-outline-variant/20 hover:bg-surface-container-highest text-on-surface transition-all" aria-label="Compartir" id="anime-modal-share-btn">
                                <span class="material-symbols-outlined text-[22px]">share</span>
                            </button>
                        </div>
                    </div>

                </div>

                {{-- Footer Detail Row --}}
                <div class="mt-auto pt-5 grid grid-cols-2 gap-4 border-t border-outline-variant/10">
                    <div>
                        <p class="text-[10px] font-label uppercase tracking-widest text-on-surface-variant mb-1">Type</p>
                        <p class="text-sm font-bold text-white">{{ $anime->type ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-label uppercase tracking-widest text-on-surface-variant mb-1">Year</p>
                        <p class="text-sm font-bold text-white">{{ $anime->released_year ?? 'N/A' }}</p>
                    </div>
                </div>

            </div>

        @else
            {{-- Loading skeleton --}}
            <div class="w-full p-16 flex items-center justify-center">
                <div class="flex flex-col items-center gap-4 text-on-surface-variant">
                    <span class="material-symbols-outlined text-[48px] animate-pulse text-primary">movie</span>
                    <p class="text-sm font-label">Loading anime information...</p>
                </div>
            </div>
        @endif

    </div>
</div>
