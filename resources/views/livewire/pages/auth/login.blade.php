<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="space-y-2">

    {{-- Heading --}}
    <div class="mb-6">
        <h1 class="font-headline text-2xl font-bold text-[var(--color-on-surface)]">Iniciar sesión</h1>
        <p class="text-sm text-[var(--color-on-surface-variant)] mt-1">Bienvenido de nuevo. Ingresa tus credenciales.</p>
    </div>

    {{-- Session Status --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">

        {{-- Email --}}
        <div>
            <x-input-label for="email" :value="__('Correo electrónico')" />
            <x-text-input
                wire:model="form.email"
                id="email"
                class="block w-full"
                type="email"
                name="email"
                required
                autofocus
                autocomplete="username"
                placeholder="tu@email.com"
            />
            <x-input-error :messages="$errors->get('form.email')" />
        </div>

        {{-- Password --}}
        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Contraseña')" />
                @if (Route::has('password.request'))
                    <a
                        class="text-xs text-[var(--color-on-surface-variant)] hover:text-[var(--color-primary)] transition-colors duration-150"
                        href="{{ route('password.request') }}"
                        wire:navigate
                    >
                        {{ __('¿Olvidaste tu contraseña?') }}
                    </a>
                @endif
            </div>
            <x-text-input
                wire:model="form.password"
                id="password"
                class="block w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
            />
            <x-input-error :messages="$errors->get('form.password')" />
        </div>

        {{-- Remember Me --}}
        <div class="flex items-center gap-2.5">
            <div class="relative flex items-center">
                <input
                    wire:model="form.remember"
                    id="remember"
                    type="checkbox"
                    name="remember"
                    class="w-4 h-4 rounded
                        bg-[var(--color-surface-container)] border-[var(--color-outline-variant)]
                        text-[var(--color-primary)]
                        focus:ring-2 focus:ring-[var(--color-primary)] focus:ring-offset-[var(--color-surface)]
                        transition duration-150 cursor-pointer"
                >
            </div>
            <label for="remember" class="text-sm text-[var(--color-on-surface-variant)] cursor-pointer select-none">
                {{ __('Recordarme') }}
            </label>
        </div>

        {{-- Submit --}}
        <x-primary-button class="w-full mt-2">
            {{ __('Iniciar sesión') }}
        </x-primary-button>

    </form>

    {{-- Register link --}}
    <p class="text-center text-sm text-[var(--color-on-surface-variant)] pt-2">
        {{ __('¿No tienes cuenta?') }}
        <a
            href="{{ route('register') }}"
            wire:navigate
            class="font-medium text-[var(--color-primary)] hover:text-[var(--color-primary-dim)] transition-colors duration-150"
        >
            {{ __('Regístrate') }}
        </a>
    </p>

</div>
