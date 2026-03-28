@props(['animes'])

<section
    x-data="{
        active: 0,
        total: {{ $animes->count() }},
        next()      { this.active = (this.active + 1) % this.total },
        prev()      { this.active = (this.active - 1 + this.total) % this.total },
        isCenter(i) { return this.active === i },
        isPrev(i)   { return this.active === (i + 1) % this.total },
        isNext(i)   { return this.active === (i - 1 + this.total) % this.total },
        isHidden(i) { return !this.isCenter(i) && !this.isPrev(i) && !this.isNext(i) }
    }"
    class="relative h-[680px] lg:h-[720px] flex items-center justify-center overflow-hidden mb-20 pt-20"
>
    <div class="absolute inset-0 opacity-20 blur-3xl scale-110 pointer-events-none overflow-hidden">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 rounded-full mix-blend-screen bg-primary"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 rounded-full mix-blend-screen bg-tertiary"></div>
    </div>

    <button
        @click.prevent="prev()"
        class="absolute left-2 sm:left-8 z-30 flex items-center justify-center p-2 sm:p-3 rounded-full bg-surface-variant/40 hover:bg-surface-variant backdrop-blur-md text-white border border-white/10 transition-all duration-200 shadow-xl outline-none"
        aria-label="Anterior"
    >
        <span class="material-symbols-outlined text-[24px] sm:text-[32px]">chevron_left</span>
    </button>

    <div class="hero-perspective relative w-full h-full max-w-7xl mx-auto">

        @foreach($animes as $index => $anime)
            <div
                :class="{
                    'hero-card-center': isCenter({{ $index }}),
                    'hero-card-prev': isPrev({{ $index }}),
                    'hero-card-next': isNext({{ $index }}),
                    'hero-card-hidden': isHidden({{ $index }})
                }"
                @click="active = {{ $index }}"
                class="absolute transition-all duration-700 ease-[cubic-bezier(0.25,1,0.5,1)] rounded-2xl overflow-hidden ring-1 ring-white/[0.06]"
            >
                <img
                    src="{{ $anime->image_url }}"
                    alt="{{ $anime->title }}"
                    class="w-full h-full object-cover transition-transform duration-1000"
                    onerror="this.style.background='linear-gradient(135deg,#1c253e,#0c1326)';this.removeAttribute('src')"
                >

                <div
                    x-show="isCenter({{ $index }})"
                    x-transition:enter="transition ease-out duration-500 delay-200"
                    x-transition:enter-start="opacity-0 translate-y-8"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-4"
                    class="absolute inset-0 scrim-gradient flex flex-col justify-end p-8 sm:p-10"
                >
                    <div class="space-y-4">

                        <span class="inline-block px-3 py-1 text-[10px] font-bold tracking-widest uppercase rounded-md backdrop-blur-md bg-tertiary/20 text-tertiary border border-tertiary/30">
                            Trending Now
                        </span>

                        <h1 class="font-headline font-extrabold text-white leading-tight text-3xl sm:text-4xl">
                            {{ $anime->title }}
                        </h1>

                        <p class="text-sm line-clamp-2 max-w-xs text-on-surface-variant">
                            {{ $anime->description }}
                        </p>

                        <div class="flex gap-3 pt-1 flex-wrap">
                            <button class="primary-gradient text-on-primary font-headline font-bold px-6 py-2.5 rounded-full flex items-center gap-2 transition-all duration-200 active:scale-95 hover:shadow-lg hover:shadow-primary/20 text-sm">
                                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">play_arrow</span>
                                Where To Watch
                            </button>
                            <button class="bg-white/10 backdrop-blur-md text-white border border-white/20 font-headline font-bold px-5 py-2.5 rounded-full flex items-center gap-2 transition-all duration-200 hover:bg-white/20 text-sm">
                                <span class="material-symbols-outlined text-[18px]">add</span>
                                Add to List
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        @endforeach

    </div>

    <button
        @click.prevent="next()"
        class="absolute right-2 sm:right-8 z-30 flex items-center justify-center p-2 sm:p-3 rounded-full bg-surface-variant/40 hover:bg-surface-variant backdrop-blur-md text-white border border-white/10 transition-all duration-200 shadow-xl outline-none"
        aria-label="Siguiente"
    >
        <span class="material-symbols-outlined text-[24px] sm:text-[32px]">chevron_right</span>
    </button>

    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-30 flex items-center gap-2">
        @foreach($animes as $index => $anime)
            <button
                @click="active = {{ $index }}"
                :class="isCenter({{ $index }}) ? 'w-6 bg-primary' : 'w-2 bg-white/30 hover:bg-white/60'"
                class="h-2 rounded-full transition-all duration-300"
                aria-label="Ir a anime {{ $index + 1 }}"
            ></button>
        @endforeach
    </div>

</section>
