@props([
    'title',
    'route' => '#',
])

<div class="section-accent flex justify-between items-center mb-6">

    {{-- Título de la sección --}}
    <h2 class="font-headline font-extrabold text-white tracking-tight uppercase text-lg">
        {{ $title }}
    </h2>

    {{-- Botón View All — wire:navigate listo para routing real --}}
    <a
        href="{{ $route }}"
        wire:navigate
        class="text-xs font-semibold text-primary transition-all group"
    >
        View All
        <span class="inline-block transition-transform duration-200 group-hover:translate-x-1">→</span>
    </a>

</div>
