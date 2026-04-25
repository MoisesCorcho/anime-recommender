@props([
    'message' => 'Loading...',
    'target'  => null,
])

<div
    @if($target) wire:loading wire:target="{{ $target }}" @else wire:loading @endif
    class="absolute inset-0 z-50 bg-surface/50 backdrop-blur-sm rounded-2xl flex items-start justify-center pt-24"
>
    <div class="bg-surface-container-high px-6 py-4 rounded-full shadow-2xl flex items-center gap-3 border border-outline-variant/10">
        <span class="material-symbols-outlined text-primary animate-spin">sync</span>
        <span class="font-bold text-sm text-on-surface tracking-wider uppercase">{{ $message }}</span>
    </div>
</div>
