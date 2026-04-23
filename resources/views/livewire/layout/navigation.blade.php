<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false, dropdownOpen: false }"
     class="fixed top-0 w-full z-50 bg-slate-950/80 backdrop-blur-md shadow-2xl shadow-black/20"
     style="border-bottom: 1px solid rgba(65, 71, 91, 0.3);">

    <div class="flex justify-between items-center px-6 sm:px-8 h-20 max-w-full mx-auto">

        {{-- LEFT: Brand + Nav Links --}}
        <div class="flex items-center gap-8 lg:gap-12">

            {{-- Brand Logo --}}
            <a href="{{ route('dashboard') }}" wire:navigate class="shrink-0">
                <span class="text-xl sm:text-2xl font-black tracking-tighter text-white uppercase font-headline">
                    Anime Recommender
                </span>
            </a>

            {{-- Primary Nav Links (desktop) --}}
            <div class="hidden md:flex gap-6 lg:gap-8 items-center">
                <x-app-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                    Discover
                </x-app-nav-link>

                @if(Route::has('directory'))
                    <x-app-nav-link href="{{ route('directory') }}" :active="request()->routeIs('directory')">
                        Directory
                    </x-app-nav-link>
                @else
                    <x-app-nav-link href="#">Directory</x-app-nav-link>
                @endif

                @if(Route::has('my-lists'))
                    <x-app-nav-link href="{{ route('my-lists') }}" :active="request()->routeIs('my-lists')">
                        My Lists
                    </x-app-nav-link>
                @endif
            </div>
        </div>

        {{-- RIGHT: Actions + User --}}
        <div class="flex items-center gap-3 sm:gap-5">

            {{-- NSFW Toggle (Desktop) --}}
            <div
                x-data="{ nsfw: {{ Session::get('show_nsfw', false) ? 'true' : 'false' }} }"
                class="hidden md:flex items-center gap-2"
                title="{{ Session::get('show_nsfw', false) ? 'Hide NSFW content' : 'Show NSFW content' }}"
            >
                <span class="hidden sm:block font-headline text-sm font-medium tracking-tight text-slate-400 select-none">NSFW</span>
                <button
                    @click="nsfw = !nsfw; Livewire.dispatch('nsfw-toggled', { value: nsfw })"
                    :aria-pressed="nsfw.toString()"
                    aria-label="Toggle NSFW content"
                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-300 focus:outline-none cursor-pointer"
                    :class="nsfw ? 'bg-rose-500/80' : 'bg-slate-700'"
                >
                    <span
                        class="inline-block h-3.5 w-3.5 rounded-full bg-white shadow-sm transition-transform duration-300"
                        :class="nsfw ? 'translate-x-[18px]' : 'translate-x-[3px]'"
                    ></span>
                </button>
            </div>

            {{-- Search Button --}}
            <button @click="$dispatch('open-search-modal')" class="flex p-2 hover:bg-slate-800/50 rounded-lg transition-all duration-300 text-slate-400 hover:text-slate-200">
                <span class="material-symbols-outlined text-[22px]">search</span>
            </button>

            {{-- Notifications Button --}}
            <button class="hidden sm:flex p-2 hover:bg-slate-800/50 rounded-lg transition-all duration-300 text-slate-400 hover:text-slate-200">
                <span class="material-symbols-outlined text-[22px]">notifications</span>
            </button>

            {{-- User Avatar Dropdown (Desktop) --}}
            <div class="hidden sm:block relative"
                 x-data="{{ json_encode(['name' => auth()->user()->name, 'email' => auth()->user()->email]) }}"
                 x-on:profile-updated.window="name = $event.detail.name"
                 @click.away="dropdownOpen = false">

                <button @click="dropdownOpen = !dropdownOpen" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-full ring-2 ring-indigo-500/40 bg-indigo-600 flex items-center justify-center overflow-hidden hover:ring-indigo-400 transition-all duration-200">
                        <span class="text-xs font-bold text-white uppercase font-headline" x-text="name.charAt(0)"></span>
                    </div>
                    <span class="text-sm text-slate-300 font-medium font-headline hidden lg:block" x-text="name"></span>
                    <span class="material-symbols-outlined text-[16px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': dropdownOpen }">expand_more</span>
                </button>

                {{-- Dropdown Menu --}}
                <div x-show="dropdownOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-3 w-52 rounded-xl overflow-hidden shadow-2xl shadow-black/50 glass-panel"
                     style="background: rgba(17, 25, 46, 0.95); border: 1px solid rgba(65, 71, 91, 0.5); top: 100%;"
                     @click="dropdownOpen = false">

                    {{-- User Info Header --}}
                    <div class="px-4 py-3 border-b" style="border-color: rgba(65, 71, 91, 0.4);">
                        <p class="text-xs text-slate-500 font-body">Signed in as</p>
                        <p class="text-sm font-semibold text-white font-headline truncate mt-0.5" x-text="name"></p>
                        <p class="text-xs text-slate-400 truncate" x-text="email"></p>
                    </div>

                    {{-- Menu Items --}}
                    <div class="py-1.5">
                        <a href="{{ route('profile') }}"
                           wire:navigate
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-300 hover:text-white hover:bg-white/5 transition-colors font-body">
                            <span class="material-symbols-outlined text-[18px] text-slate-500">manage_accounts</span>
                            Profile Settings
                        </a>
                        <div style="height: 1px; background: rgba(65, 71, 91, 0.4); margin: 4px 16px;"></div>
                        <button wire:click="logout"
                                class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/5 transition-colors font-body">
                            <span class="material-symbols-outlined text-[18px]">logout</span>
                            Log Out
                        </button>
                    </div>
                </div>
            </div>

            {{-- Mobile Hamburger --}}
            <button @click="open = !open"
                    class="md:hidden flex items-center justify-center p-2 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/50 transition-all duration-200">
                <span class="material-symbols-outlined" x-show="!open">menu</span>
                <span class="material-symbols-outlined" x-show="open" x-cloak>close</span>
            </button>
        </div>
    </div>

    {{-- MOBILE MENU --}}
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="border-top: 1px solid rgba(65, 71, 91, 0.3); background: rgba(7, 13, 31, 0.98);"
         class="md:hidden glass-panel">

        {{-- Mobile Nav Links --}}
        <div class="px-6 py-4 space-y-1">
            <x-app-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="explore" :mobile="true">
                Discover
            </x-app-nav-link>

            @if(Route::has('directory'))
                <x-app-nav-link href="{{ route('directory') }}" :active="request()->routeIs('directory')" icon="grid_view" :mobile="true">
                    Directory
                </x-app-nav-link>
            @else
                <x-app-nav-link href="#" icon="grid_view" :mobile="true">Directory</x-app-nav-link>
            @endif

            @if(Route::has('my-lists'))
                <x-app-nav-link href="{{ route('my-lists') }}" :active="request()->routeIs('my-lists')" icon="bookmarks" :mobile="true">
                    My List
                </x-app-nav-link>
            @endif

            {{-- Mobile NSFW Toggle --}}
            <div
                x-data="{ nsfw: {{ Session::get('show_nsfw', false) ? 'true' : 'false' }} }"
                class="flex items-center justify-between px-3 py-2.5 mt-2 rounded-lg"
            >
                <div class="flex items-center gap-3 text-slate-400">
                    <span class="material-symbols-outlined text-[18px]">visibility_off</span>
                    <span class="text-sm font-medium font-headline">NSFW Content</span>
                </div>
                <button
                    @click="nsfw = !nsfw; Livewire.dispatch('nsfw-toggled', { value: nsfw })"
                    :aria-pressed="nsfw.toString()"
                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-300 focus:outline-none cursor-pointer"
                    :class="nsfw ? 'bg-rose-500/80' : 'bg-slate-700'"
                >
                    <span
                        class="inline-block h-3.5 w-3.5 rounded-full bg-white shadow-sm transition-transform duration-300"
                        :class="nsfw ? 'translate-x-[18px]' : 'translate-x-[3px]'"
                    ></span>
                </button>
            </div>
        </div>

        {{-- Mobile User Section --}}
        <div style="border-top: 1px solid rgba(65, 71, 91, 0.3);"
             class="px-6 py-4"
             x-data="{{ json_encode(['name' => auth()->user()->name, 'email' => auth()->user()->email]) }}"
             x-on:profile-updated.window="name = $event.detail.name">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full ring-2 ring-indigo-500/40 bg-indigo-600 flex items-center justify-center">
                    <span class="text-xs font-bold text-white uppercase font-headline" x-text="name.charAt(0)"></span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white font-headline" x-text="name"></p>
                    <p class="text-xs text-slate-400" x-text="email"></p>
                </div>
            </div>
            <div class="space-y-1">
                <a href="{{ route('profile') }}"
                   wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-400 hover:text-white hover:bg-white/5 transition-colors font-body">
                    <span class="material-symbols-outlined text-[18px]">manage_accounts</span>
                    Profile Settings
                </a>
                <button wire:click="logout"
                        class="flex items-center gap-3 w-full px-3 py-2 rounded-lg text-sm text-red-400 hover:text-red-300 hover:bg-red-500/5 transition-colors font-body">
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                    Log Out
                </button>
            </div>
        </div>
    </div>
</nav>
