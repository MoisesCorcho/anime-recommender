@props([
    'align'          => 'right',
    'width'          => '48',
    'contentClasses' => '',
])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right inset-s-0',
    'top'  => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left inset-e-0',
};

$widthClass = match ($width) {
    '48'    => 'w-48',
    '56'    => 'w-56',
    default => $width,
};
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="absolute z-50 mt-2 {{ $widthClass }} rounded-xl shadow-xl {{ $alignmentClasses }}"
        style="display: none;"
        @click="open = false"
    >
        <div class="rounded-xl overflow-hidden
            bg-[var(--color-surface-container-high)]
            border border-[var(--color-outline-variant)]/20
            shadow-[0_8px_32px_rgba(0,0,0,0.4)]
            ring-1 ring-black ring-opacity-10
            {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
