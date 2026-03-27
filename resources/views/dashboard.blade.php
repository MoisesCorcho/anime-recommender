<x-app-layout>

{{-- *** Override the global min-h-screen background from the layout *** --}}
@push('styles')
<style>
    /* The app layout wraps content in a min-h-screen bg-gray-100 div.
       We override it to be transparent so our custom background shows. */
    body > div {
        background-color: transparent !important;
    }
</style>
@endpush

<div class="pb-24" style="background-color: #070d1f; color: #dfe4fe; min-height: 100vh;">

    {{-- ══════════════════════════════════════════════════════════
         HERO SECTION — Featured anime with 3D perspective cards
         ══════════════════════════════════════════════════════════ --}}
    <section class="relative h-[680px] lg:h-[720px] flex items-center justify-center overflow-hidden mb-20"
             style="padding-top: 80px;">

        {{-- Background ambient glow --}}
        <div class="absolute inset-0 opacity-20 blur-3xl scale-110 pointer-events-none overflow-hidden">
            <div class="absolute top-1/4 left-1/4 w-96 h-96 rounded-full mix-blend-screen"
                 style="background: #9fa7ff;"></div>
            <div class="absolute bottom-1/4 right-1/4 w-96 h-96 rounded-full mix-blend-screen"
                 style="background: #ffa9e0;"></div>
        </div>

        {{-- 3D card cluster --}}
        <div class="hero-perspective container mx-auto flex items-center justify-center gap-6 lg:gap-8 px-4 relative z-10">

            {{-- Side card LEFT (decorative, half-visible) --}}
            <div class="hidden lg:block w-[260px] h-[400px] rounded-2xl overflow-hidden shadow-2xl opacity-40 border"
                 style="transform: rotateY(12deg) translateX(-20px) scale(0.92); border-color: rgba(255,255,255,0.06); transition: all .5s ease;">
                <img src="https://upload.wikimedia.org/wikipedia/en/thumb/d/db/Steins_gate_0_visual_novel_cover.jpg/220px-Steins_gate_0_visual_novel_cover.jpg"
                     alt="Anime Cover 1"
                     class="w-full h-full object-cover"
                     onerror="this.style.background='linear-gradient(135deg,#1c253e,#0c1326)';this.removeAttribute('src')">
            </div>

            {{-- FEATURED center card --}}
            <div class="relative w-[360px] sm:w-[420px] h-[520px] sm:h-[580px] rounded-2xl overflow-hidden z-10 ring-1 group"
                 style="box-shadow: 0 32px 64px -16px rgba(0,0,0,0.85); ring-color: rgba(255,255,255,0.1); transform: scale(1.06); transition: all .7s ease;">

                {{-- Poster image —uses a well-known public anime cover --}}
                <img src="https://upload.wikimedia.org/wikipedia/en/thumb/c/c9/Fullmetal_Alchemist_Brotherhood_DVD_vol_1.jpg/220px-Fullmetal_Alchemist_Brotherhood_DVD_vol_1.jpg"
                     alt="Featured Anime Cover"
                     class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105"
                     onerror="this.style.background='linear-gradient(135deg,#1a1040,#0c1326)';this.removeAttribute('src')">

                {{-- Scrim overlay + text --}}
                <div class="absolute inset-0 scrim-gradient flex flex-col justify-end p-8 sm:p-10">
                    <div class="space-y-4" style="transform: translateY(6px); transition: transform .5s ease;">
                        {{-- Badge --}}
                        <span class="inline-block px-3 py-1 text-[10px] font-bold tracking-widest uppercase rounded-md backdrop-blur-md border"
                              style="background: rgba(255,169,224,0.15); color: #ffa9e0; border-color: rgba(255,169,224,0.3);">
                            Trending Now
                        </span>

                        {{-- Title --}}
                        <h1 class="font-headline font-extrabold text-white leading-tight"
                            style="font-size: clamp(1.5rem, 4vw, 2.2rem);">
                            Fullmetal Alchemist: Brotherhood
                        </h1>

                        {{-- Description --}}
                        <p class="text-sm line-clamp-2 max-w-xs" style="color: #a5aac2;">
                            Two brothers seek the Philosopher's Stone to restore their bodies after a failed alchemical ritual. A battle of sacrifice, truth, and humanity begins.
                        </p>

                        {{-- Action Buttons --}}
                        <div class="flex gap-3 pt-1 flex-wrap">
                            <button class="primary-gradient font-headline font-bold px-6 py-2.5 rounded-full flex items-center gap-2 transition-all duration-200 active:scale-95 hover:shadow-lg text-sm"
                                    style="color: #101b8b; hover:box-shadow: 0 0 20px rgba(159,167,255,0.4);">
                                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">play_arrow</span>
                                Watch Trailer
                            </button>
                            <button class="font-headline font-bold px-5 py-2.5 rounded-full flex items-center gap-2 border transition-all duration-200 text-sm text-white hover:bg-white/20"
                                    style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); backdrop-filter: blur(8px);">
                                <span class="material-symbols-outlined text-[18px]">add</span>
                                Add to List
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Side card RIGHT (decorative, half-visible) --}}
            <div class="hidden lg:block w-[260px] h-[400px] rounded-2xl overflow-hidden shadow-2xl opacity-40 border"
                 style="transform: rotateY(-12deg) translateX(20px) scale(0.92); border-color: rgba(255,255,255,0.06); transition: all .5s ease;">
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
                <div class="flex justify-between items-center mb-6" style="border-left: 4px solid #6366f1; padding-left: 1rem;">
                    <h2 class="font-headline font-extrabold text-white tracking-tight uppercase text-lg">
                        Most Popular
                    </h2>
                    <button class="text-xs font-semibold transition-all group" style="color: #818cf8;">
                        View All
                        <span class="inline-block transition-transform duration-200 group-hover:translate-x-1">→</span>
                    </button>
                </div>

                {{-- 2×2 Card Grid --}}
                <div class="grid grid-cols-2 gap-4">

                    {{-- Card: Attack on Titan --}}
                    <div class="group relative card-shadow-glow cursor-pointer transition-all duration-500 hover:scale-[1.03] rounded-2xl overflow-hidden border"
                         style="aspect-ratio: 3/4; border-color: rgba(30,41,59,0.8);">
                        <img src="https://upload.wikimedia.org/wikipedia/en/thumb/d/d6/Shingeki_no_kyojin_manga_volume_1_cover.jpg/170px-Shingeki_no_kyojin_manga_volume_1_cover.jpg"
                             alt="Attack on Titan"
                             class="w-full h-full object-cover"
                             onerror="this.style.background='linear-gradient(135deg,#1e3a5f,#0c1326)';this.removeAttribute('src')">
                        <div class="absolute inset-0 flex flex-col justify-end p-4"
                             style="background: linear-gradient(to top, rgba(0,0,0,0.92) 0%, transparent 55%);">
                            <div class="backdrop-blur-sm -mx-4 -mb-4 px-4 py-3"
                                 style="background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.05);">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-yellow-400 text-[10px]">⭐</span>
                                    <span class="text-white text-[10px] font-bold">9.0</span>
                                </div>
                                <h3 class="text-white font-bold text-xs leading-tight line-clamp-1">Attack on Titan</h3>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Demon Slayer --}}
                    <div class="group relative card-shadow-glow cursor-pointer transition-all duration-500 hover:scale-[1.03] rounded-2xl overflow-hidden border"
                         style="aspect-ratio: 3/4; border-color: rgba(30,41,59,0.8);">
                        <img src="https://upload.wikimedia.org/wikipedia/en/thumb/1/10/Kimetsu_no_Yaiba_Manga_Chapter_1_Cover.jpg/220px-Kimetsu_no_Yaiba_Manga_Chapter_1_Cover.jpg"
                             alt="Demon Slayer"
                             class="w-full h-full object-cover"
                             onerror="this.style.background='linear-gradient(135deg,#3b1a2a,#0c1326)';this.removeAttribute('src')">
                        <div class="absolute inset-0 flex flex-col justify-end p-4"
                             style="background: linear-gradient(to top, rgba(0,0,0,0.92) 0%, transparent 55%);">
                            <div class="backdrop-blur-sm -mx-4 -mb-4 px-4 py-3"
                                 style="background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.05);">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-yellow-400 text-[10px]">⭐</span>
                                    <span class="text-white text-[10px] font-bold">8.7</span>
                                </div>
                                <h3 class="text-white font-bold text-xs leading-tight line-clamp-1">Demon Slayer</h3>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Death Note --}}
                    <div class="group relative card-shadow-glow cursor-pointer transition-all duration-500 hover:scale-[1.03] rounded-2xl overflow-hidden border"
                         style="aspect-ratio: 3/4; border-color: rgba(30,41,59,0.8);">
                        <img src="https://upload.wikimedia.org/wikipedia/en/thumb/1/1d/Dr._Stone_Volume_1.png/220px-Dr._Stone_Volume_1.png"
                             alt="Dr. Stone"
                             class="w-full h-full object-cover"
                             onerror="this.style.background='linear-gradient(135deg,#1a1a2e,#0c1326)';this.removeAttribute('src')">
                        <div class="absolute inset-0 flex flex-col justify-end p-4"
                             style="background: linear-gradient(to top, rgba(0,0,0,0.92) 0%, transparent 55%);">
                            <div class="backdrop-blur-sm -mx-4 -mb-4 px-4 py-3"
                                 style="background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.05);">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-yellow-400 text-[10px]">⭐</span>
                                    <span class="text-white text-[10px] font-bold">8.2</span>
                                </div>
                                <h3 class="text-white font-bold text-xs leading-tight line-clamp-1">Dr. Stone</h3>
                            </div>
                        </div>
                    </div>

                    {{-- Card: One Punch Man --}}
                    <div class="group relative card-shadow-glow cursor-pointer transition-all duration-500 hover:scale-[1.03] rounded-2xl overflow-hidden border"
                         style="aspect-ratio: 3/4; border-color: rgba(30,41,59,0.8);">
                        <img src="https://upload.wikimedia.org/wikipedia/en/thumb/0/02/One_Punch_Man_Volume_1_Cover.png/220px-One_Punch_Man_Volume_1_Cover.png"
                             alt="One Punch Man"
                             class="w-full h-full object-cover"
                             onerror="this.style.background='linear-gradient(135deg,#2d1b4e,#0c1326)';this.removeAttribute('src')">
                        <div class="absolute inset-0 flex flex-col justify-end p-4"
                             style="background: linear-gradient(to top, rgba(0,0,0,0.92) 0%, transparent 55%);">
                            <div class="backdrop-blur-sm -mx-4 -mb-4 px-4 py-3"
                                 style="background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.05);">
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
                <div class="flex justify-between items-center mb-6" style="border-left: 4px solid #6366f1; padding-left: 1rem;">
                    <h2 class="font-headline font-extrabold text-white tracking-tight uppercase text-lg">
                        Recommended for You
                    </h2>
                    <button class="text-xs font-semibold transition-all group" style="color: #818cf8;">
                        View All
                        <span class="inline-block transition-transform duration-200 group-hover:translate-x-1">→</span>
                    </button>
                </div>

                {{-- 2×2 Card Grid --}}
                <div class="grid grid-cols-2 gap-4">

                    {{-- Card: Steins;Gate --}}
                    <div class="group relative card-shadow-glow cursor-pointer transition-all duration-500 hover:scale-[1.03] rounded-2xl overflow-hidden border"
                         style="aspect-ratio: 3/4; border-color: rgba(30,41,59,0.8);">
                        <img src="https://upload.wikimedia.org/wikipedia/en/thumb/d/db/Steins_gate_0_visual_novel_cover.jpg/220px-Steins_gate_0_visual_novel_cover.jpg"
                             alt="Steins Gate"
                             class="w-full h-full object-cover"
                             onerror="this.style.background='linear-gradient(135deg,#0f2027,#0c1326)';this.removeAttribute('src')">
                        <div class="absolute inset-0 flex flex-col justify-end p-4"
                             style="background: linear-gradient(to top, rgba(0,0,0,0.92) 0%, transparent 55%);">
                            <div class="backdrop-blur-sm -mx-4 -mb-4 px-4 py-3"
                                 style="background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.05);">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-yellow-400 text-[10px]">⭐</span>
                                    <span class="text-white text-[10px] font-bold">9.1</span>
                                </div>
                                <h3 class="text-white font-bold text-xs leading-tight line-clamp-1">Steins;Gate</h3>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Hunter x Hunter --}}
                    <div class="group relative card-shadow-glow cursor-pointer transition-all duration-500 hover:scale-[1.03] rounded-2xl overflow-hidden border"
                         style="aspect-ratio: 3/4; border-color: rgba(30,41,59,0.8);">
                        <img src="https://upload.wikimedia.org/wikipedia/en/thumb/4/4d/Attack_on_Titan_manga_volume_1.jpg/170px-Attack_on_Titan_manga_volume_1.jpg"
                             alt="Hunter x Hunter"
                             class="w-full h-full object-cover"
                             onerror="this.style.background='linear-gradient(135deg,#1a3a2a,#0c1326)';this.removeAttribute('src')">
                        <div class="absolute inset-0 flex flex-col justify-end p-4"
                             style="background: linear-gradient(to top, rgba(0,0,0,0.92) 0%, transparent 55%);">
                            <div class="backdrop-blur-sm -mx-4 -mb-4 px-4 py-3"
                                 style="background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.05);">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-yellow-400 text-[10px]">⭐</span>
                                    <span class="text-white text-[10px] font-bold">9.0</span>
                                </div>
                                <h3 class="text-white font-bold text-xs leading-tight line-clamp-1">Hunter × Hunter</h3>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Violet Evergarden --}}
                    <div class="group relative card-shadow-glow cursor-pointer transition-all duration-500 hover:scale-[1.03] rounded-2xl overflow-hidden border"
                         style="aspect-ratio: 3/4; border-color: rgba(30,41,59,0.8);">
                        <img src="https://upload.wikimedia.org/wikipedia/en/thumb/c/c9/Fullmetal_Alchemist_Brotherhood_DVD_vol_1.jpg/220px-Fullmetal_Alchemist_Brotherhood_DVD_vol_1.jpg"
                             alt="Violet Evergarden"
                             class="w-full h-full object-cover"
                             onerror="this.style.background='linear-gradient(135deg,#1a2a3a,#0c1326)';this.removeAttribute('src')">
                        <div class="absolute inset-0 flex flex-col justify-end p-4"
                             style="background: linear-gradient(to top, rgba(0,0,0,0.92) 0%, transparent 55%);">
                            <div class="backdrop-blur-sm -mx-4 -mb-4 px-4 py-3"
                                 style="background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.05);">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-yellow-400 text-[10px]">⭐</span>
                                    <span class="text-white text-[10px] font-bold">8.9</span>
                                </div>
                                <h3 class="text-white font-bold text-xs leading-tight line-clamp-1">Violet Evergarden</h3>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Vinland Saga --}}
                    <div class="group relative card-shadow-glow cursor-pointer transition-all duration-500 hover:scale-[1.03] rounded-2xl overflow-hidden border"
                         style="aspect-ratio: 3/4; border-color: rgba(30,41,59,0.8);">
                        <img src="https://upload.wikimedia.org/wikipedia/en/thumb/1/10/Kimetsu_no_Yaiba_Manga_Chapter_1_Cover.jpg/220px-Kimetsu_no_Yaiba_Manga_Chapter_1_Cover.jpg"
                             alt="Vinland Saga"
                             class="w-full h-full object-cover"
                             onerror="this.style.background='linear-gradient(135deg,#2a1a0a,#0c1326)';this.removeAttribute('src')">
                        <div class="absolute inset-0 flex flex-col justify-end p-4"
                             style="background: linear-gradient(to top, rgba(0,0,0,0.92) 0%, transparent 55%);">
                            <div class="backdrop-blur-sm -mx-4 -mb-4 px-4 py-3"
                                 style="background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.05);">
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
    <footer class="max-w-7xl mx-auto px-6 sm:px-8 py-12 mt-12 flex flex-col sm:flex-row justify-between items-center gap-4"
            style="border-top: 1px solid rgba(65,71,91,0.15); color: rgba(165,170,194,0.4);">
        <div class="flex items-center gap-4">
            <span class="text-base font-black tracking-tighter uppercase font-headline">The Curator</span>
            <span class="text-[10px] uppercase tracking-widest">© {{ date('Y') }} Premium Tier Experience</span>
        </div>
        <div class="flex gap-6 sm:gap-8 text-[11px] font-bold uppercase tracking-widest">
            <a href="#" class="transition-colors duration-200 hover:text-indigo-400">Privacy</a>
            <a href="#" class="transition-colors duration-200 hover:text-indigo-400">Terms</a>
            <a href="#" class="transition-colors duration-200 hover:text-indigo-400">Support</a>
        </div>
    </footer>

</div>

</x-app-layout>

