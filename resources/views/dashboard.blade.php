<x-app-layout>

<div class="min-h-screen pb-24 bg-surface text-on-surface">

    <x-dashboard.hero
        featuredTitle="Fullmetal Alchemist: Brotherhood"
        featuredDesc="Two brothers seek the Philosopher's Stone to restore their bodies after a failed alchemical ritual. A battle of sacrifice, truth, and humanity begins."
        featuredImage="https://upload.wikimedia.org/wikipedia/en/thumb/c/c9/Fullmetal_Alchemist_Brotherhood_DVD_vol_1.jpg/220px-Fullmetal_Alchemist_Brotherhood_DVD_vol_1.jpg"
        leftImage="https://upload.wikimedia.org/wikipedia/en/thumb/d/db/Steins_gate_0_visual_novel_cover.jpg/220px-Steins_gate_0_visual_novel_cover.jpg"
        rightImage="https://upload.wikimedia.org/wikipedia/en/thumb/4/4d/Attack_on_Titan_manga_volume_1.jpg/170px-Attack_on_Titan_manga_volume_1.jpg"
    />

    <div class="max-w-7xl mx-auto px-6 sm:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14">

            {{-- ─────────────────────────
                 LEFT: Most Popular
                 ───────────────────────── --}}
            <section>
                <x-section-header title="Most Popular" route="#" />

                <div class="grid grid-cols-2 gap-4">
                    <x-anime-card
                        title="Attack on Titan"
                        score="9.0"
                        image="https://upload.wikimedia.org/wikipedia/en/thumb/d/d6/Shingeki_no_kyojin_manga_volume_1_cover.jpg/170px-Shingeki_no_kyojin_manga_volume_1_cover.jpg"
                    />
                    <x-anime-card
                        title="Demon Slayer"
                        score="8.7"
                        image="https://upload.wikimedia.org/wikipedia/en/thumb/1/10/Kimetsu_no_Yaiba_Manga_Chapter_1_Cover.jpg/220px-Kimetsu_no_Yaiba_Manga_Chapter_1_Cover.jpg"
                    />
                    <x-anime-card
                        title="Dr. Stone"
                        score="8.2"
                        image="https://upload.wikimedia.org/wikipedia/en/thumb/1/1d/Dr._Stone_Volume_1.png/220px-Dr._Stone_Volume_1.png"
                    />
                    <x-anime-card
                        title="One Punch Man"
                        score="8.8"
                        image="https://upload.wikimedia.org/wikipedia/en/thumb/0/02/One_Punch_Man_Volume_1_Cover.png/220px-One_Punch_Man_Volume_1_Cover.png"
                    />
                </div>
            </section>

            {{-- ─────────────────────────
                 RIGHT: Recommended for You
                 ───────────────────────── --}}
            <section>
                <x-section-header title="Recommended for You" route="#" />

                <div class="grid grid-cols-2 gap-4">
                    <x-anime-card
                        title="Steins;Gate"
                        score="9.1"
                        image="https://upload.wikimedia.org/wikipedia/en/thumb/d/db/Steins_gate_0_visual_novel_cover.jpg/220px-Steins_gate_0_visual_novel_cover.jpg"
                    />
                    <x-anime-card
                        title="Hunter × Hunter"
                        score="9.0"
                        image="https://upload.wikimedia.org/wikipedia/en/thumb/4/4d/Attack_on_Titan_manga_volume_1.jpg/170px-Attack_on_Titan_manga_volume_1.jpg"
                    />
                    <x-anime-card
                        title="Violet Evergarden"
                        score="8.9"
                        image="https://upload.wikimedia.org/wikipedia/en/thumb/c/c9/Fullmetal_Alchemist_Brotherhood_DVD_vol_1.jpg/220px-Fullmetal_Alchemist_Brotherhood_DVD_vol_1.jpg"
                    />
                    <x-anime-card
                        title="Vinland Saga"
                        score="8.8"
                        image="https://upload.wikimedia.org/wikipedia/en/thumb/1/10/Kimetsu_no_Yaiba_Manga_Chapter_1_Cover.jpg/220px-Kimetsu_no_Yaiba_Manga_Chapter_1_Cover.jpg"
                    />
                </div>
            </section>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         FOOTER — Componente global reutilizable.
         ══════════════════════════════════════════════════════════ --}}
    <x-footer />

</div>

</x-app-layout>
