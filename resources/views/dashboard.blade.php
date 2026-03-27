<x-app-layout>

<div class="min-h-screen pb-24 bg-surface text-on-surface">

    {{-- ══════════════════════════════════════════════════════════
         HERO SECTION — Featured anime with 3D perspective cards
         ══════════════════════════════════════════════════════════ --}}
    <section class="relative h-[680px] lg:h-[720px] flex items-center justify-center overflow-hidden mb-20 pt-20">

        {{-- Background ambient glow --}}
        <div class="absolute inset-0 opacity-20 blur-3xl scale-110 pointer-events-none overflow-hidden">
            <div class="absolute top-1/4 left-1/4 w-96 h-96 rounded-full mix-blend-screen bg-primary"></div>
            <div class="absolute bottom-1/4 right-1/4 w-96 h-96 rounded-full mix-blend-screen bg-tertiary"></div>
        </div>

        {{-- 3D card cluster --}}
        <div class="hero-perspective container mx-auto flex items-center justify-center gap-6 lg:gap-8 px-4 relative z-10">

            {{-- Side card LEFT (decorative, half-visible) --}}
            <div class="hero-card-left hidden lg:block w-[260px] h-[400px] rounded-2xl overflow-hidden shadow-2xl opacity-40 border border-white/[0.06]">
                <img src="https://upload.wikimedia.org/wikipedia/en/thumb/d/db/Steins_gate_0_visual_novel_cover.jpg/220px-Steins_gate_0_visual_novel_cover.jpg"
                     alt="Anime Cover 1"
                     class="w-full h-full object-cover"
                     onerror="this.style.background='linear-gradient(135deg,#1c253e,#0c1326)';this.removeAttribute('src')">
            </div>

            {{-- FEATURED center card --}}
            <div class="hero-card-center relative w-[360px] sm:w-[420px] h-[520px] sm:h-[580px] rounded-2xl overflow-hidden z-10 ring-1 ring-white/10 shadow-[0_32px_64px_-16px_rgba(0,0,0,0.85)] group">

                {{-- Poster image --}}
                <img src="https://upload.wikimedia.org/wikipedia/en/thumb/c/c9/Fullmetal_Alchemist_Brotherhood_DVD_vol_1.jpg/220px-Fullmetal_Alchemist_Brotherhood_DVD_vol_1.jpg"
                     alt="Featured Anime Cover"
                     class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105"
                     onerror="this.style.background='linear-gradient(135deg,#1a1040,#0c1326)';this.removeAttribute('src')">

                {{-- Scrim overlay + text --}}
                <div class="absolute inset-0 scrim-gradient flex flex-col justify-end p-8 sm:p-10">
                    <div class="space-y-4 translate-y-1.5 transition-transform duration-500 group-hover:translate-y-0">
                        {{-- Badge --}}
                        <span class="inline-block px-3 py-1 text-[10px] font-bold tracking-widest uppercase rounded-md backdrop-blur-md bg-tertiary/20 text-tertiary border border-tertiary/30">
                            Trending Now
                        </span>

                        {{-- Title --}}
                        <h1 class="font-headline font-extrabold text-white leading-tight text-3xl sm:text-4xl">
                            Fullmetal Alchemist: Brotherhood
                        </h1>

                        {{-- Description --}}
                        <p class="text-sm line-clamp-2 max-w-xs text-on-surface-variant">
                            Two brothers seek the Philosopher's Stone to restore their bodies after a failed alchemical ritual. A battle of sacrifice, truth, and humanity begins.
                        </p>

                        {{-- Action Buttons --}}
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

            {{-- Side card RIGHT (decorative, half-visible) --}}
            <div class="hero-card-right hidden lg:block w-[260px] h-[400px] rounded-2xl overflow-hidden shadow-2xl opacity-40 border border-white/[0.06]">
                <img src="https://upload.wikimedia.org/wikipedia/en/thumb/4/4d/Attack_on_Titan_manga_volume_1.jpg/170px-Attack_on_Titan_manga_volume_1.jpg"
                     alt="Anime Cover 2"
                     class="w-full h-full object-cover"
                     onerror="this.style.background='linear-gradient(135deg,#1c253e,#0c1326)';this.removeAttribute('src')">
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════
         MAIN CONTENT — Two-column grid sections
         ══════════════════════════════════════════════════════════ --}}
    <div class="max-w-7xl mx-auto px-6 sm:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14">

            {{-- ─────────────────────────
                 LEFT: Most Popular
                 ───────────────────────── --}}
            <section>
                {{-- Section Header --}}
                <div class="section-accent flex justify-between items-center mb-6">
                    <h2 class="font-headline font-extrabold text-white tracking-tight uppercase text-lg">
                        Most Popular
                    </h2>
                    <button class="text-xs font-semibold text-primary transition-all group">
                        View All
                        <span class="inline-block transition-transform duration-200 group-hover:translate-x-1">→</span>
                    </button>
                </div>

                {{-- 2×2 Card Grid --}}
                <div class="grid grid-cols-2 gap-4">

                    {{-- Card: Attack on Titan --}}
                    <div class="group relative card-shadow-glow cursor-pointer transition-all duration-500 hover:scale-[1.03] rounded-2xl overflow-hidden border border-surface-variant aspect-[3/4]">
                        <img src="https://upload.wikimedia.org/wikipedia/en/thumb/d/d6/Shingeki_no_kyojin_manga_volume_1_cover.jpg/170px-Shingeki_no_kyojin_manga_volume_1_cover.jpg"
                             alt="Attack on Titan"
                             class="w-full h-full object-cover"
                             onerror="this.style.background='linear-gradient(135deg,#1e3a5f,#0c1326)';this.removeAttribute('src')">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent flex flex-col justify-end p-4">
                            <div class="backdrop-blur-sm bg-black/20 -mx-4 -mb-4 px-4 py-3 border-t border-white/5">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-yellow-400 text-[10px]">⭐</span>
                                    <span class="text-white text-[10px] font-bold">9.0</span>
                                </div>
                                <h3 class="text-white font-bold text-xs leading-tight line-clamp-1">Attack on Titan</h3>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Demon Slayer --}}
                    <div class="group relative card-shadow-glow cursor-pointer transition-all duration-500 hover:scale-[1.03] rounded-2xl overflow-hidden border border-surface-variant aspect-[3/4]">
                        <img src="https://upload.wikimedia.org/wikipedia/en/thumb/1/10/Kimetsu_no_Yaiba_Manga_Chapter_1_Cover.jpg/220px-Kimetsu_no_Yaiba_Manga_Chapter_1_Cover.jpg"
                             alt="Demon Slayer"
                             class="w-full h-full object-cover"
                             onerror="this.style.background='linear-gradient(135deg,#3b1a2a,#0c1326)';this.removeAttribute('src')">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent flex flex-col justify-end p-4">
                            <div class="backdrop-blur-sm bg-black/20 -mx-4 -mb-4 px-4 py-3 border-t border-white/5">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-yellow-400 text-[10px]">⭐</span>
                                    <span class="text-white text-[10px] font-bold">8.7</span>
                                </div>
                                <h3 class="text-white font-bold text-xs leading-tight line-clamp-1">Demon Slayer</h3>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Dr. Stone --}}
                    <div class="group relative card-shadow-glow cursor-pointer transition-all duration-500 hover:scale-[1.03] rounded-2xl overflow-hidden border border-surface-variant aspect-[3/4]">
                        <img src="https://upload.wikimedia.org/wikipedia/en/thumb/1/1d/Dr._Stone_Volume_1.png/220px-Dr._Stone_Volume_1.png"
                             alt="Dr. Stone"
                             class="w-full h-full object-cover"
                             onerror="this.style.background='linear-gradient(135deg,#1a1a2e,#0c1326)';this.removeAttribute('src')">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent flex flex-col justify-end p-4">
                            <div class="backdrop-blur-sm bg-black/20 -mx-4 -mb-4 px-4 py-3 border-t border-white/5">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-yellow-400 text-[10px]">⭐</span>
                                    <span class="text-white text-[10px] font-bold">8.2</span>
                                </div>
                                <h3 class="text-white font-bold text-xs leading-tight line-clamp-1">Dr. Stone</h3>
                            </div>
                        </div>
                    </div>

                    {{-- Card: One Punch Man --}}
                    <div class="group relative card-shadow-glow cursor-pointer transition-all duration-500 hover:scale-[1.03] rounded-2xl overflow-hidden border border-surface-variant aspect-[3/4]">
                        <img src="https://upload.wikimedia.org/wikipedia/en/thumb/0/02/One_Punch_Man_Volume_1_Cover.png/220px-One_Punch_Man_Volume_1_Cover.png"
                             alt="One Punch Man"
                             class="w-full h-full object-cover"
                             onerror="this.style.background='linear-gradient(135deg,#2d1b4e,#0c1326)';this.removeAttribute('src')">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent flex flex-col justify-end p-4">
                            <div class="backdrop-blur-sm bg-black/20 -mx-4 -mb-4 px-4 py-3 border-t border-white/5">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-yellow-400 text-[10px]">⭐</span>
                                    <span class="text-white text-[10px] font-bold">8.8</span>
                                </div>
                                <h3 class="text-white font-bold text-xs leading-tight line-clamp-1">One Punch Man</h3>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            {{-- ─────────────────────────
                 RIGHT: Recommended for You
                 ───────────────────────── --}}
            <section>
                {{-- Section Header --}}
                <div class="section-accent flex justify-between items-center mb-6">
                    <h2 class="font-headline font-extrabold text-white tracking-tight uppercase text-lg">
                        Recommended for You
                    </h2>
                    <button class="text-xs font-semibold text-primary transition-all group">
                        View All
                        <span class="inline-block transition-transform duration-200 group-hover:translate-x-1">→</span>
                    </button>
                </div>

                {{-- 2×2 Card Grid --}}
                <div class="grid grid-cols-2 gap-4">

                    {{-- Card: Steins;Gate --}}
                    <div class="group relative card-shadow-glow cursor-pointer transition-all duration-500 hover:scale-[1.03] rounded-2xl overflow-hidden border border-surface-variant aspect-[3/4]">
                        <img src="https://upload.wikimedia.org/wikipedia/en/thumb/d/db/Steins_gate_0_visual_novel_cover.jpg/220px-Steins_gate_0_visual_novel_cover.jpg"
                             alt="Steins Gate"
                             class="w-full h-full object-cover"
                             onerror="this.style.background='linear-gradient(135deg,#0f2027,#0c1326)';this.removeAttribute('src')">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent flex flex-col justify-end p-4">
                            <div class="backdrop-blur-sm bg-black/20 -mx-4 -mb-4 px-4 py-3 border-t border-white/5">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-yellow-400 text-[10px]">⭐</span>
                                    <span class="text-white text-[10px] font-bold">9.1</span>
                                </div>
                                <h3 class="text-white font-bold text-xs leading-tight line-clamp-1">Steins;Gate</h3>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Hunter x Hunter --}}
                    <div class="group relative card-shadow-glow cursor-pointer transition-all duration-500 hover:scale-[1.03] rounded-2xl overflow-hidden border border-surface-variant aspect-[3/4]">
                        <img src="https://upload.wikimedia.org/wikipedia/en/thumb/4/4d/Attack_on_Titan_manga_volume_1.jpg/170px-Attack_on_Titan_manga_volume_1.jpg"
                             alt="Hunter x Hunter"
                             class="w-full h-full object-cover"
                             onerror="this.style.background='linear-gradient(135deg,#1a3a2a,#0c1326)';this.removeAttribute('src')">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent flex flex-col justify-end p-4">
                            <div class="backdrop-blur-sm bg-black/20 -mx-4 -mb-4 px-4 py-3 border-t border-white/5">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-yellow-400 text-[10px]">⭐</span>
                                    <span class="text-white text-[10px] font-bold">9.0</span>
                                </div>
                                <h3 class="text-white font-bold text-xs leading-tight line-clamp-1">Hunter × Hunter</h3>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Violet Evergarden --}}
                    <div class="group relative card-shadow-glow cursor-pointer transition-all duration-500 hover:scale-[1.03] rounded-2xl overflow-hidden border border-surface-variant aspect-[3/4]">
                        <img src="https://upload.wikimedia.org/wikipedia/en/thumb/c/c9/Fullmetal_Alchemist_Brotherhood_DVD_vol_1.jpg/220px-Fullmetal_Alchemist_Brotherhood_DVD_vol_1.jpg"
                             alt="Violet Evergarden"
                             class="w-full h-full object-cover"
                             onerror="this.style.background='linear-gradient(135deg,#1a2a3a,#0c1326)';this.removeAttribute('src')">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent flex flex-col justify-end p-4">
                            <div class="backdrop-blur-sm bg-black/20 -mx-4 -mb-4 px-4 py-3 border-t border-white/5">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-yellow-400 text-[10px]">⭐</span>
                                    <span class="text-white text-[10px] font-bold">8.9</span>
                                </div>
                                <h3 class="text-white font-bold text-xs leading-tight line-clamp-1">Violet Evergarden</h3>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Vinland Saga --}}
                    <div class="group relative card-shadow-glow cursor-pointer transition-all duration-500 hover:scale-[1.03] rounded-2xl overflow-hidden border border-surface-variant aspect-[3/4]">
                        <img src="https://upload.wikimedia.org/wikipedia/en/thumb/1/10/Kimetsu_no_Yaiba_Manga_Chapter_1_Cover.jpg/220px-Kimetsu_no_Yaiba_Manga_Chapter_1_Cover.jpg"
                             alt="Vinland Saga"
                             class="w-full h-full object-cover"
                             onerror="this.style.background='linear-gradient(135deg,#2a1a0a,#0c1326)';this.removeAttribute('src')">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent flex flex-col justify-end p-4">
                            <div class="backdrop-blur-sm bg-black/20 -mx-4 -mb-4 px-4 py-3 border-t border-white/5">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-yellow-400 text-[10px]">⭐</span>
                                    <span class="text-white text-[10px] font-bold">8.8</span>
                                </div>
                                <h3 class="text-white font-bold text-xs leading-tight line-clamp-1">Vinland Saga</h3>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         FOOTER
         ══════════════════════════════════════════════════════════ --}}
    <footer class="max-w-7xl mx-auto px-6 sm:px-8 py-12 mt-12 flex flex-col sm:flex-row justify-between items-center gap-4 border-t border-outline-variant/15 text-on-surface-variant/40">
        <div class="flex items-center gap-4">
            <span class="text-base font-black tracking-tighter uppercase font-headline">The Curator</span>
            <span class="text-[10px] uppercase tracking-widest">© {{ date('Y') }} Premium Tier Experience</span>
        </div>
        <div class="flex gap-6 sm:gap-8 text-[11px] font-bold uppercase tracking-widest">
            <a href="#" class="transition-colors duration-200 hover:text-primary">Privacy</a>
            <a href="#" class="transition-colors duration-200 hover:text-primary">Terms</a>
            <a href="#" class="transition-colors duration-200 hover:text-primary">Support</a>
        </div>
    </footer>

</div>

</x-app-layout>
