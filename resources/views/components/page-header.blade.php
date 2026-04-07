@props([
    'title',
    'description' => null,
    'icon'        => null,
])

<header class="mb-8 md:mb-12">

    {{-- Pre-header slot: e.g. a "Back" button --}}
    {{ $before ?? '' }}

    {{-- Title row --}}
    <div class="flex items-center gap-3 mb-2">
        @if($icon)
            <span class="material-symbols-outlined text-primary text-4xl">{{ $icon }}</span>
        @endif
        <h1 class="font-headline text-3xl md:text-4xl font-extrabold tracking-tight text-white">
            {{ $title }}
        </h1>
    </div>

    @if($description)
        <p class="text-on-surface-variant font-body text-sm md:text-base max-w-2xl">
            {{ $description }}
        </p>
    @endif

    {{-- After-header slot: e.g. search bar, action buttons --}}
    {{ $after ?? '' }}

</header>
