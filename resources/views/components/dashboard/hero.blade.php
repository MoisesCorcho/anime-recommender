@props([
    'featuredTitle',
    'featuredDesc',
    'featuredImage',
    'leftImage',
    'rightImage',
])

<section class="relative h-[680px] lg:h-[720px] flex items-center justify-center overflow-hidden mb-20 pt-20">

    {{-- Halos de color ambientales --}}
    <div class="absolute inset-0 opacity-20 blur-3xl scale-110 pointer-events-none overflow-hidden">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 rounded-full mix-blend-screen bg-primary"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 rounded-full mix-blend-screen bg-tertiary"></div>
    </div>

    {{-- Clúster de tarjetas 3D --}}
    <div class="hero-perspective container mx-auto flex items-center justify-center gap-6 lg:gap-8 px-4 relative z-10">

        {{-- Tarjeta decorativa IZQUIERDA --}}
        <div class="hero-card-left hidden lg:block w-[260px] h-[400px] rounded-2xl overflow-hidden shadow-2xl opacity-40 border border-white/[0.06]">
            <img
                src="{{ $leftImage }}"
                alt="Featured Anime Side Cover"
                class="w-full h-full object-cover"
                onerror="this.style.background='linear-gradient(135deg,#1c253e,#0c1326)';this.removeAttribute('src')"
            >
        </div>

        {{-- Tarjeta CENTRAL (anime destacado) --}}
        <div class="hero-card-center relative w-[360px] sm:w-[420px] h-[520px] sm:h-[580px] rounded-2xl overflow-hidden z-10 ring-1 ring-white/10 shadow-[0_32px_64px_-16px_rgba(0,0,0,0.85)] group">

            {{-- Póster principal --}}
            <img
                src="{{ $featuredImage }}"
                alt="{{ $featuredTitle }}"
                class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105"
                onerror="this.style.background='linear-gradient(135deg,#1a1040,#0c1326)';this.removeAttribute('src')"
            >

            {{-- Scrim + información del anime --}}
            <div class="absolute inset-0 scrim-gradient flex flex-col justify-end p-8 sm:p-10">
                <div class="space-y-4 translate-y-1.5 transition-transform duration-500 group-hover:translate-y-0">

                    {{-- Badge "Trending Now" --}}
                    <span class="inline-block px-3 py-1 text-[10px] font-bold tracking-widest uppercase rounded-md backdrop-blur-md bg-tertiary/20 text-tertiary border border-tertiary/30">
                        Trending Now
                    </span>

                    {{-- Título del anime --}}
                    <h1 class="font-headline font-extrabold text-white leading-tight text-3xl sm:text-4xl">
                        {{ $featuredTitle }}
                    </h1>

                    {{-- Descripción breve --}}
                    <p class="text-sm line-clamp-2 max-w-xs text-on-surface-variant">
                        {{ $featuredDesc }}
                    </p>

                    {{-- Botones de acción --}}
                    <div class="flex gap-3 pt-1 flex-wrap">
                        <button class="primary-gradient text-on-primary font-headline font-bold px-6 py-2.5 rounded-full flex items-center gap-2 transition-all duration-200 active:scale-95 hover:shadow-lg hover:shadow-primary/20 text-sm">
                            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">play_arrow</span>
                            Watch Trailer
                        </button>
                        <button class="bg-white/10 backdrop-blur-md text-white border border-white/20 font-headline font-bold px-5 py-2.5 rounded-full flex items-center gap-2 transition-all duration-200 hover:bg-white/20 text-sm">
                            <span class="material-symbols-outlined text-[18px]">add</span>
                            Add to List
                        </button>
                    </div>

                </div>
            </div>
        </div>

        {{-- Tarjeta decorativa DERECHA --}}
        <div class="hero-card-right hidden lg:block w-[260px] h-[400px] rounded-2xl overflow-hidden shadow-2xl opacity-40 border border-white/[0.06]">
            <img
                src="{{ $rightImage }}"
                alt="Featured Anime Side Cover"
                class="w-full h-full object-cover"
                onerror="this.style.background='linear-gradient(135deg,#1c253e,#0c1326)';this.removeAttribute('src')"
            >
        </div>

    </div>
</section>
