@props([
    'icon'        => 'search_off',
    'title',
    'description' => null,
])

<div class="py-24 md:py-32 flex flex-col items-center justify-center text-center opacity-70">
    <span class="material-symbols-outlined text-[64px] text-primary mb-6">{{ $icon }}</span>
    <p class="text-white font-headline font-semibold text-2xl">{{ $title }}</p>
    @if($description)
        <p class="text-sm text-on-surface-variant mt-2 max-w-md mx-auto">{{ $description }}</p>
    @endif
    {{-- Optional action buttons (e.g. "Clear Filters", "Browse Directory") --}}
    {{ $slot }}
</div>
