@props([
    'label',
    'variant' => 'default', // 'default' | 'primary' | 'tertiary' | 'error'
])

@php
    $classes = match($variant) {
        'primary'  => 'bg-primary-container/20 text-primary-fixed border-primary/30',
        'tertiary' => 'bg-tertiary/10 text-tertiary border-tertiary/20',
        'error'    => 'bg-error/10 text-error border-error/20',
        default    => 'bg-surface-variant text-on-surface-variant border-outline-variant/20',
    };
@endphp

<span class="inline-flex items-center px-3 py-1 rounded-md text-[10px] font-label uppercase tracking-widest font-bold border {{ $classes }}">
    {{ $label }}
</span>
