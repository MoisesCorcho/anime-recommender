<footer class="max-w-7xl mx-auto px-6 sm:px-8 py-12 mt-12 flex flex-col sm:flex-row justify-between items-center gap-4 border-t border-outline-variant/15 text-on-surface-variant/40">

    {{-- Marca y logo textual --}}
    <div class="flex items-center gap-4">
        <span class="text-base font-black tracking-tighter uppercase font-headline">Anime Recommender</span>
        <span class="text-[10px] uppercase tracking-widest">© {{ date('Y') }} Premium Tier Experience</span>
    </div>

    {{-- Links legales --}}
    <nav class="flex gap-6 sm:gap-8 text-[11px] font-bold uppercase tracking-widest">
        <a href="#" wire:navigate class="transition-colors duration-200 hover:text-primary">Privacy</a>
        <a href="#" wire:navigate class="transition-colors duration-200 hover:text-primary">Terms</a>
        <a href="#" wire:navigate class="transition-colors duration-200 hover:text-primary">Support</a>
    </nav>

</footer>
