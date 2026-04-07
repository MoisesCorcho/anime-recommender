@props([
    'anime',
    'showFavoriteBadge' => false,
    'wireKey'           => null,
])

<div
    wire:click="$dispatch('open-anime-modal', '{{ $anime->id }}')"
    @if($wireKey) wire:key="{{ $wireKey }}" @endif
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
        <div class="absolute inset-0 bg-gradient-to-t from-surface-container-lowest via-transparent to-transparent
                    opacity-0 group-hover:opacity-100 transition-opacity duration-300
                    flex flex-col justify-end p-4 md:p-5">

            @php
                $genres = is_string($anime->genres) ? json_decode($anime->genres, true) : $anime->genres;
            @endphp

            <span class="text-tertiary text-[9px] md:text-[10px] font-bold tracking-[0.2em] uppercase mb-1 drop-shadow-md">
                {{ $anime->type ?? 'TV' }}
                @if(is_array($genres) && count($genres) > 0) • {{ $genres[0] }} @endif
            </span>

            <h3 class="font-headline text-base md:text-lg font-bold leading-tight text-white drop-shadow-lg">
                {{ $anime->title }}
            </h3>
        </div>

        {{-- Favorite badge (optional) --}}
        @if($showFavoriteBadge)
            <div class="absolute top-2.5 left-2.5 z-10">
                <span class="material-symbols-outlined material-filled text-rose-400 text-[18px] drop-shadow-md">favorite</span>
            </div>
        @endif
    </div>

    {{-- Details below image (hidden on hover) --}}
    <div class="mt-3 md:mt-4 group-hover:opacity-0 transition-opacity duration-200 px-1">
        <h4 class="font-headline text-sm font-bold text-white truncate" title="{{ $anime->title }}">
            {{ $anime->title }}
        </h4>
        <p class="text-on-surface-variant text-[11px] md:text-xs mt-1 font-medium">
            {{ $anime->released_year ?: 'N/A' }}
            @if($anime->episodes) • {{ $anime->episodes }} Eps @endif
        </p>
    </div>
</div>
