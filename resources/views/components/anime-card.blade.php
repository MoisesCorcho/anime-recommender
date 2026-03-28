@props([
    'title',
    'image',
    'score',
])

<div
    class="group relative card-shadow-glow cursor-pointer transition-all duration-500 hover:scale-[1.03] rounded-2xl overflow-hidden border border-surface-variant aspect-[3/4]"
>
    {{-- Póster del anime --}}
    <img
        src="{{ $image }}"
        alt="{{ $title }}"
        class="w-full h-full object-cover"
        onerror="this.style.background='linear-gradient(135deg,#1c253e,#0c1326)';this.removeAttribute('src')"
    >

    {{-- Scrim inferior + metadatos --}}
    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent flex flex-col justify-end p-4">
        <div class="backdrop-blur-sm bg-black/20 -mx-4 -mb-4 px-4 py-3 border-t border-white/5">

            {{-- Score --}}
            <div class="flex items-center gap-1.5 mb-0.5">
                <span class="text-yellow-400 text-[10px]">⭐</span>
                <span class="text-white text-[10px] font-bold">{{ $score }}</span>
            </div>

            {{-- Título --}}
            <h3 class="text-white font-bold text-xs leading-tight line-clamp-1">{{ $title }}</h3>
        </div>
    </div>
</div>
