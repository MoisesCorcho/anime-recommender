<?php

use App\Enums\InteractionType;
use App\Enums\UserAnimeStatus;
use App\Jobs\LogUserInteractionJob;
use App\Models\Anime;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, on};

state([
    'showModal'   => false,
    'anime'       => null,
    'userStatus'  => null,      // UserAnimeStatus value string or null
    'isFavorite'  => false,
    'showToast'   => false,
    'toastMessage' => '',
]);

on(['open-anime-modal' => function (string $id) {
    $this->anime      = Anime::find($id);
    $this->showModal  = true;
    $this->userStatus = null;
    $this->isFavorite = false;

    if (Auth::check() && $this->anime) {
        $user = Auth::user();
        $pivot = $user->animes()
            ->where('anime_id', $this->anime->id)
            ->first()?->pivot;

        if ($pivot) {
            $this->userStatus = $pivot->status?->value;
            $this->isFavorite = (bool) $pivot->is_favorite;
        }

        // Log the view interaction asynchronously
        LogUserInteractionJob::dispatch(
            user: $user,
            type: InteractionType::AnimeView,
            payload: InteractionType::animeViewPayload(animeId: (string) $this->anime->id)
        );
    }
}]);

$updateStatus = function (string $status) {
    if (! Auth::check() || ! $this->anime) return;

    Auth::user()->animes()->syncWithoutDetaching([
        $this->anime->id => ['status' => $status],
    ]);

    $this->userStatus = $status;

    $labels = [
        UserAnimeStatus::PlanToWatch->value => 'Plan to Watch',
        UserAnimeStatus::Watching->value    => 'Watching',
        UserAnimeStatus::OnHold->value      => 'On Hold',
        UserAnimeStatus::Completed->value   => 'Completed',
        UserAnimeStatus::Dropped->value     => 'Dropped',
    ];

    $this->toastMessage = 'Added to ' . ($labels[$status] ?? $status);
    $this->showToast    = true;
};

$toggleFavorite = function () {
    if (! Auth::check() || ! $this->anime) return;

    $this->isFavorite = ! $this->isFavorite;
    $user = Auth::user();

    $user->animes()->syncWithoutDetaching([
        $this->anime->id => ['is_favorite' => $this->isFavorite],
    ]);

    // Log favorite addition interaction
    if ($this->isFavorite) {
        LogUserInteractionJob::dispatch(
            user: $user,
            type: InteractionType::FavoriteAdd,
            payload: InteractionType::favoriteAddPayload(animeId: (string) $this->anime->id)
        );
    }

    $this->toastMessage = $this->isFavorite ? 'Added to Favorites' : 'Removed from Favorites';
    $this->showToast    = true;
};

$markAsDropped = function () {
    if (! Auth::check() || ! $this->anime) return;

    Auth::user()->animes()->syncWithoutDetaching([
        $this->anime->id => ['status' => UserAnimeStatus::Dropped->value],
    ]);

    $this->userStatus   = UserAnimeStatus::Dropped->value;
    $this->toastMessage = 'Marked as Dropped';
    $this->showToast    = true;
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
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4 md:p-8"
    id="anime-detail-modal"
    role="dialog"
    aria-modal="true"
>
    {{-- Glass Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-md" @click="show = false"></div>

    {{-- Toast Notification --}}
    <div
        wire:poll.1500ms="$set('showToast', false)"
        x-show="$wire.showToast"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[10000]
               flex items-center gap-2.5 px-5 py-3 rounded-full
               bg-[var(--color-surface-container-highest)]
               border border-[var(--color-outline-variant)]/20
               shadow-[0_8px_32px_rgba(0,0,0,0.5)]
               text-sm font-medium text-[var(--color-on-surface)]"
        style="display: none;"
    >
        <span class="material-symbols-outlined material-filled text-[var(--color-primary)] text-[18px]">check_circle</span>
        <span>{{ $toastMessage }}</span>
    </div>

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

                    {{-- Type and Year --}}
                    <p class="font-label text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-primary)]">
                        {{ $anime->type ?? 'Series' }}
                        @if($anime->released_year)
                            <span class="text-[var(--color-on-surface-variant)] mx-1.5">•</span>
                            {{ $anime->released_year }}
                        @endif
                    </p>

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

                        {{-- Add to List Dropdown --}}
                        <x-dropdown align="left" width="56">
                            <x-slot name="trigger">
                                <button
                                    id="anime-modal-add-list-btn"
                                    class="flex items-center gap-2 px-6 py-3.5 rounded-full font-bold text-sm
                                           shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 transition-transform
                                           {{ $userStatus
                                               ? 'bg-[var(--color-surface-container-highest)] text-[var(--color-primary)] border border-[var(--color-primary)]/30'
                                               : 'bg-gradient-to-br from-primary to-primary-container text-on-primary'
                                           }}"
                                >
                                    <span class="material-symbols-outlined material-filled text-[20px]">
                                        {{ $userStatus ? 'playlist_add_check' : 'playlist_add' }}
                                    </span>
                                    {{ $userStatus
                                        ? match($userStatus) {
                                            'PLAN_TO_WATCH' => 'Plan to Watch',
                                            'WATCHING'      => 'Watching',
                                            'ON_HOLD'       => 'On Hold',
                                            'COMPLETED'     => 'Completed',
                                            'DROPPED'       => 'Dropped',
                                            default         => 'In My List',
                                          }
                                        : 'Add to List'
                                    }}
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <div class="py-1">
                                    <p class="px-4 pt-2 pb-1 text-[10px] font-semibold uppercase tracking-widest text-[var(--color-on-surface-variant)]">
                                        My Lists
                                    </p>
                                    <x-dropdown-link wire:click="updateStatus('{{ \App\Enums\UserAnimeStatus::PlanToWatch->value }}')">
                                        <span class="material-symbols-outlined text-[18px] text-[var(--color-primary)]">bookmark</span>
                                        Plan to Watch
                                        @if($userStatus === \App\Enums\UserAnimeStatus::PlanToWatch->value)
                                            <span class="material-symbols-outlined material-filled text-[16px] text-[var(--color-primary)] ml-auto">check</span>
                                        @endif
                                    </x-dropdown-link>
                                    <x-dropdown-link wire:click="updateStatus('{{ \App\Enums\UserAnimeStatus::Watching->value }}')">
                                        <span class="material-symbols-outlined text-[18px] text-[var(--color-primary)]">play_circle</span>
                                        Watching
                                        @if($userStatus === \App\Enums\UserAnimeStatus::Watching->value)
                                            <span class="material-symbols-outlined material-filled text-[16px] text-[var(--color-primary)] ml-auto">check</span>
                                        @endif
                                    </x-dropdown-link>
                                    <x-dropdown-link wire:click="updateStatus('{{ \App\Enums\UserAnimeStatus::OnHold->value }}')">
                                        <span class="material-symbols-outlined text-[18px] text-[var(--color-primary)]">pause_circle</span>
                                        On Hold
                                        @if($userStatus === \App\Enums\UserAnimeStatus::OnHold->value)
                                            <span class="material-symbols-outlined material-filled text-[16px] text-[var(--color-primary)] ml-auto">check</span>
                                        @endif
                                    </x-dropdown-link>
                                    <x-dropdown-link wire:click="updateStatus('{{ \App\Enums\UserAnimeStatus::Completed->value }}')">
                                        <span class="material-symbols-outlined text-[18px] text-[var(--color-primary)]">check_circle</span>
                                        Completed
                                        @if($userStatus === \App\Enums\UserAnimeStatus::Completed->value)
                                            <span class="material-symbols-outlined material-filled text-[16px] text-[var(--color-primary)] ml-auto">check</span>
                                        @endif
                                    </x-dropdown-link>
                                </div>
                            </x-slot>
                        </x-dropdown>

                        {{-- Icon Buttons --}}
                        <div class="flex items-center gap-3">

                            {{-- Favorite --}}
                            <button
                                wire:click="toggleFavorite"
                                class="flex items-center justify-center p-3.5 rounded-full border transition-all
                                       {{ $isFavorite
                                           ? 'bg-[var(--color-tertiary)]/10 border-[var(--color-tertiary)]/30 text-[var(--color-tertiary)]'
                                           : 'border-outline-variant/20 hover:bg-surface-container-highest text-on-surface'
                                       }}"
                                aria-label="{{ $isFavorite ? 'Quitar de favoritos' : 'Añadir a favoritos' }}"
                                id="anime-modal-favorite-btn"
                            >
                                <span class="material-symbols-outlined text-[22px] {{ $isFavorite ? 'material-filled' : '' }}">
                                    favorite
                                </span>
                            </button>

                            {{-- Dropped --}}
                            <button
                                wire:click="markAsDropped"
                                class="flex items-center justify-center p-3.5 rounded-full border transition-all
                                       {{ $userStatus === \App\Enums\UserAnimeStatus::Dropped->value
                                           ? 'bg-[var(--color-error)]/10 border-[var(--color-error)]/30 text-[var(--color-error)]'
                                           : 'border-outline-variant/20 hover:bg-error/10 hover:text-error hover:border-error/30 text-on-surface'
                                       }}"
                                aria-label="Marcar como Dropped"
                                id="anime-modal-dropped-btn"
                            >
                                <span class="material-symbols-outlined text-[22px]">block</span>
                            </button>

                        </div>
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
