@props([
    'href',
    'active'  => false,
    'icon'    => null,       // Material Symbol name
    'mobile'  => false,      // renders the mobile (full row) variant
])

@if($mobile)
    {{-- Mobile row variant --}}
    <a
        href="{{ $href }}"
        wire:navigate
        {{ $attributes->merge(['class' => 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium font-headline transition-colors ' . ($active ? 'text-white bg-indigo-500/15 text-indigo-300' : 'text-slate-400 hover:text-white hover:bg-white/5')]) }}
    >
        @if($icon)
            <span class="material-symbols-outlined text-[18px]">{{ $icon }}</span>
        @endif
        {{ $slot }}
    </a>
@else
    {{-- Desktop inline variant --}}
    <a
        href="{{ $href }}"
        wire:navigate
        {{ $attributes->merge(['class' => 'font-headline text-sm font-medium tracking-tight transition-colors duration-200 ' . ($active ? 'text-white border-b-2 border-indigo-500 pb-1' : 'text-slate-400 hover:text-slate-200')]) }}
    >
        {{ $slot }}
    </a>
@endif
