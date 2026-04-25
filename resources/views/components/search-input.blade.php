@props([
    'model',
    'placeholder'  => 'Search...',
    'debounce'     => '300ms',
    'id'           => null,
    'size'         => 'md', // 'sm' | 'md' | 'lg'
    'autofocus'    => false,
    'defer'        => false,
])

@php
    $sizeClasses = match($size) {
        'sm'    => 'px-4 py-3 gap-3',
        'lg'    => 'px-5 py-4 gap-4',
        default => 'px-5 py-3.5 gap-4',
    };

    $iconSize = match($size) {
        'sm'    => 'text-[20px]',
        'lg'    => 'text-[24px]',
        default => 'text-[22px]',
    };

    $textSize = match($size) {
        'sm'    => 'text-sm',
        'lg'    => 'text-lg',
        default => 'text-base',
    };
@endphp

<div class="flex items-center {{ $sizeClasses }} bg-surface-container-lowest rounded-xl
            focus-within:ring-2 ring-primary transition-all shadow-inner w-full">

    <span class="material-symbols-outlined text-primary {{ $iconSize }} flex-shrink-0">search</span>

    <input
        @if($defer)
            wire:model="{{ $model }}"
        @else
            wire:model.live.debounce.{{ $debounce }}="{{ $model }}"
        @endif
        type="text"
        @if($id) id="{{ $id }}" @endif
        placeholder="{{ $placeholder }}"
        @if($autofocus) autofocus @endif
        {{ $attributes->except(['model', 'placeholder', 'debounce', 'id', 'size', 'autofocus', 'defer', 'wire:keydown.enter']) }}
        class="bg-transparent border-none focus:ring-0 text-on-surface placeholder:text-outline w-full {{ $textSize }} font-body outline-none"
    />

    {{-- Slot for optional suffix (e.g. ESC key hint) --}}
    {{ $slot }}
</div>
