<?php

use App\Actions\Animes\SearchAnimesByNaturalLanguageAction;
use App\Enums\InteractionType;
use App\Exceptions\InsufficientCreditsException;
use App\Jobs\LogUserInteractionJob;
use App\Services\CreditCheckoutService;
use App\Services\CreditService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use function Livewire\Volt\{state, on, computed, updated};

state([
    'showModal' => false,
    'showUpgradeModal' => false,
    'search' => '',
    'results' => []
]);

on(['open-search-modal' => function () {
    $this->showModal = true;
    $this->search = '';
    $this->results = [];
    $this->showUpgradeModal = false;
}]);

updated(['search' => function () {
    if (empty(trim($this->search))) {
        $this->results = [];
    }
}]);

$performSearch = function (SearchAnimesByNaturalLanguageAction $searchAction, CreditService $creditService) {
    $query = trim($this->search);
    
    if (empty($query)) {
        $this->results = [];
        return;
    }

    if (!auth()->check()) {
        return redirect()->route('login');
    }

    try {
        // Consumir un crédito por cada búsqueda semántica
        $creditService->deductForSemanticSearch(auth()->user());
    } catch (InsufficientCreditsException $e) {
        $this->showUpgradeModal = true;
        return;
    }

    // Límite de texto para prevenir abuso en el payload de la API
    $query = mb_substr($query, 0, 150);

    // Búsqueda semántica usando embeddings
    $animes = $searchAction->execute($query, 10);
    $this->results = $animes;
    
    // Registrar interacción asíncrona
    LogUserInteractionJob::dispatch(
        user: auth()->user(),
        type: InteractionType::SemanticSearch,
        payload: InteractionType::semanticSearchPayload(
            query: $query,
            resultsCount: $animes->count()
        )
    );

    // Historial de búsquedas recientes
    $recent = Session::get('recent_searches', []);
    array_unshift($recent, $query);
    $recent = array_unique($recent);
    Session::put('recent_searches', array_slice($recent, 0, 5));
};

$selectSearch = function ($term, SearchAnimesByNaturalLanguageAction $searchAction, CreditService $creditService) {
    $this->search = $term;
    $this->performSearch($searchAction, $creditService);
};

$selectAnime = function ($id) {
    $this->showModal = false;
    $this->dispatch('open-anime-modal', (string)$id);
};

$checkout = function (string $plan, CreditCheckoutService $checkoutService) {
    $dto = $checkoutService->createCheckoutSession(auth()->user(), $plan);
    return redirect()->away($dto->checkoutUrl);
};

$recentSearches = computed(function () {
    return Session::get('recent_searches', []);
});

?>

<div
    x-data="{ 
        show: $wire.entangle('showModal'),
        showUpgrade: $wire.entangle('showUpgradeModal')
    }"
    x-show="show"
    x-effect="document.body.classList.toggle('overflow-hidden', show || showUpgrade)"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    x-on:keydown.escape.window="show = false; showUpgrade = false"
    class="fixed inset-0 z-[100] flex items-start justify-center pt-[153px] px-4"
    style="display: none;"
    id="search-modal"
    role="dialog"
    aria-modal="true"
    x-cloak
>
    {{-- Background Blur Overlay --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm pointer-events-auto" @click="show = false; showUpgrade = false"></div>

    {{-- Modal Container --}}
    <div 
        x-show="!showUpgrade" 
        class="relative w-full max-w-2xl bg-surface-container-low border border-outline-variant/20 rounded-3xl shadow-[0_40px_100px_-20px_rgba(0,0,0,0.85)] overflow-hidden z-10 flex flex-col h-auto max-h-[75vh]"
    >

        {{-- Aesthetic glow blob --}}
        <div class="absolute top-0 right-0 w-full h-64 z-0 opacity-20 blur-3xl overflow-hidden pointer-events-none">
            <div class="absolute inset-0 bg-primary/40 rounded-full scale-150 -translate-y-1/2 translate-x-1/2"></div>
        </div>

        {{-- Search Input Area --}}
        <div class="p-6 border-b border-outline-variant/10 shrink-0 relative z-10 flex flex-col md:flex-row items-center gap-4">
            <div class="w-full flex-grow relative" x-data="{ textLength: 0 }">
                <x-search-input 
                    wire:keydown.enter="performSearch" 
                    model="search" 
                    placeholder="Describe the anime you want to watch (e.g. 'samurai looking for revenge')..." 
                    maxlength="150" 
                    size="lg" 
                    :autofocus="true"
                    x-on:input="textLength = $event.target.value.length"
                >
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-xs text-outline-variant font-bold mr-2" x-text="textLength + '/150'"></span>
                        <kbd class="hidden md:inline-flex items-center justify-center px-2 py-1 text-xs font-semibold text-outline-variant bg-surface-container rounded border border-outline-variant/20 uppercase tracking-widest">Esc</kbd>
                    </div>
                </x-search-input>
            </div>

            {{-- Action Button --}}
            <button wire:click="performSearch" class="flex-shrink-0 px-6 py-3.5 rounded-2xl bg-primary hover:bg-primary/90 transition-colors text-on-primary font-bold shadow-[0_0_20px_rgba(var(--color-primary),0.4)] flex items-center justify-center gap-2 border border-primary-container cursor-pointer" aria-label="AI Search">
                <span wire:loading.remove wire:target="performSearch" class="material-symbols-outlined text-[24px]">auto_awesome</span>
                <span wire:loading wire:target="performSearch" class="material-symbols-outlined text-[24px] animate-spin">sync</span>
                <span class="hidden md:block">Search</span>
            </button>

            {{-- Desktop Close Button --}}
            <button @click="show = false" class="hidden md:flex flex-shrink-0 items-center justify-center p-3 rounded-2xl bg-surface-variant hover:bg-surface-container-highest transition-colors text-on-surface-variant hover:text-white border border-outline-variant/10 cursor-pointer" aria-label="Cerrar Búsqueda" title="Close Search">
                <span class="material-symbols-outlined text-[28px]">close</span>
            </button>
        </div>

        {{-- Results Section --}}
        <div class="overflow-y-auto hide-scrollbar flex-grow min-h-0 relative z-10">

            {{-- Loading State --}}
            <div wire:loading wire:target="performSearch" class="p-12 flex flex-col items-center justify-center text-on-surface-variant gap-4">
                <span class="material-symbols-outlined text-[40px] text-primary animate-spin">sync</span>
                <p class="font-headline font-bold text-center">Analyzing the catalog with AI...</p>
            </div>

            {{-- Content Area (Hidden while loading) --}}
            <div wire:loading.remove wire:target="performSearch">
                @if(empty($results))
                    @if(empty(trim($search)))
                        @if(count($this->recentSearches) > 0)
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-xs font-label uppercase tracking-[0.2em] text-on-surface-variant font-bold">Recent Semantic Searches</h3>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($this->recentSearches as $recent)
                                        <button wire:click="selectSearch('{{ addslashes($recent) }}')" class="flex items-center gap-2 px-3 py-2 bg-surface-container-low hover:bg-surface-container text-on-surface text-sm rounded-lg border border-outline-variant/10 transition-colors">
                                            <span class="material-symbols-outlined text-[16px] text-primary">auto_awesome</span>
                                            {{ Str::limit($recent, 40) }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <x-empty-state icon="travel_explore" title="Natural Language Search" description="Describe what you feel like watching. Powered by OpenAI Embeddings." />
                        @endif
                    @else
                        <x-empty-state icon="search_off" title="No matches found" description="Try tweaking your description or using different keywords." />
                    @endif
                @else
                    <div class="p-4 space-y-1">
                        @foreach($results as $anime)
                            <div
                                wire:click="selectAnime('{{ $anime->id }}')"
                                class="group flex items-center gap-4 p-3 rounded-2xl hover:bg-indigo-500/10 hover:border-transparent transition-all cursor-pointer border border-transparent"
                            >
                                <div class="h-16 w-12 rounded-lg overflow-hidden flex-shrink-0 shadow-lg relative bg-surface-container">
                                    <img
                                        src="{{ $anime->image_url }}"
                                        alt="{{ $anime->title }}"
                                        class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                        onerror="this.style.background='linear-gradient(135deg,#1c253e,#0c1326)';this.removeAttribute('src')"
                                    >
                                </div>
                                <div class="flex-grow min-w-0">
                                    <div class="flex items-start md:items-center justify-between flex-col md:flex-row gap-1 md:gap-0">
                                        <h3 class="font-headline font-bold text-on-surface group-hover:text-primary transition-colors truncate pr-4 max-w-full">{{ $anime->title }}</h3>
                                        @if($anime->type)
                                            <div class="flex-shrink-0 hidden sm:block">
                                                <x-badge :label="$anime->type" :variant="strtolower($anime->type) === 'tv' ? 'tertiary' : 'default'" />
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex gap-2 text-sm text-on-surface-variant font-medium mt-1">
                                        @if($anime->score)
                                            <span class="flex items-center gap-1">
                                                <span class="material-symbols-outlined material-filled text-primary/80 text-[14px]">star</span>
                                                {{ number_format((float)$anime->score, 1) }}
                                            </span>
                                        @endif
                                        @if($anime->released_year)
                                            <span class="flex items-center gap-1">• {{ $anime->released_year }}</span>
                                        @endif
                                        @if($anime->type)
                                            <span class="sm:hidden flex items-center gap-1">• {{ $anime->type }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="material-symbols-outlined text-outline-variant opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">arrow_forward_ios</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Footer Hint --}}
        <div class="p-4 bg-surface-container-lowest/50 flex justify-between items-center text-[10px] font-bold text-outline tracking-widest uppercase px-8 shrink-0">
            <div class="flex gap-4">
                <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined !text-[14px]">keyboard_return</span>
                    Enter to search
                </span>
            </div>
            <div class="flex items-center gap-4">
                @auth
                    <span class="flex items-center gap-1 text-on-surface-variant">
                        <span class="material-symbols-outlined text-[14px]">token</span>
                        {{ auth()->user()->credit_balance }} Credits Left
                    </span>
                @endauth
            </div>
        </div>
    </div>

    {{-- Upgrade/Purchase Modal --}}
    <div 
        x-show="showUpgrade" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="relative w-full max-w-lg bg-surface-container-low border border-outline-variant/20 rounded-3xl shadow-2xl overflow-hidden z-20 flex flex-col p-8 items-center text-center gap-6"
    >
        <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
            <span class="material-symbols-outlined text-[32px]">token</span>
        </div>

        <div class="space-y-2">
            <h2 class="text-2xl font-headline font-bold text-on-surface">Out of Credits</h2>
            <p class="text-on-surface-variant">Semantic searches require credits. Upgrade to Pro or buy a credit pack to continue exploring with AI.</p>
        </div>

        <div class="grid grid-cols-1 gap-3 w-full mt-4">
            {{-- Pro Subscription --}}
            <button wire:click="checkout('pro')" class="group relative flex items-center justify-between p-4 rounded-2xl border border-primary/30 bg-primary/5 hover:bg-primary/10 transition-all text-left">
                <div>
                    <div class="font-bold text-on-surface flex items-center gap-2">
                        Pro Subscription
                        <span class="px-2 py-0.5 rounded-full bg-primary text-[10px] text-on-primary uppercase tracking-wider">Best Value</span>
                    </div>
                    <div class="text-xs text-on-surface-variant">5000 credits/mo + faster searches</div>
                </div>
                <span class="material-symbols-outlined text-primary group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </button>

            {{-- Credit Packs --}}
            @foreach(['pack_100' => 100, 'pack_500' => 500, 'pack_1000' => 1000] as $key => $amount)
                <button wire:click="checkout('{{ $key }}')" class="group flex items-center justify-between p-4 rounded-2xl border border-outline-variant/10 bg-surface-container hover:bg-surface-container-highest transition-all text-left">
                    <div>
                        <div class="font-bold text-on-surface">{{ $amount }} Credits Pack</div>
                        <div class="text-xs text-on-surface-variant">One-time purchase</div>
                    </div>
                    <span class="material-symbols-outlined text-outline-variant group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </button>
            @endforeach
        </div>

        <button @click="showUpgrade = false" class="text-xs font-bold text-outline-variant hover:text-on-surface uppercase tracking-widest transition-colors mt-4">
            Maybe later
        </button>
    </div>

    {{-- Close Button (Mobile Only - Floating) --}}
    <button @click="show = false; showUpgrade = false" class="absolute top-25 right-4 z-[60] p-2 bg-surface-container-highest/80 backdrop-blur-md rounded-full text-white active:bg-black/70 transition-colors flex md:hidden" aria-label="Cerrar modal">
        <span class="material-symbols-outlined text-[20px]">close</span>
    </button>
</div>

