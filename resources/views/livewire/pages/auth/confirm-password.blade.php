<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email'    => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>

    {{-- Heading --}}
    <div class="mb-6">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-[var(--color-surface-container-high)] border border-[var(--color-outline-variant)] mb-4">
            <span class="material-symbols-outlined text-[var(--color-primary)]" style="font-size:22px;">lock</span>
        </div>
        <h1 class="font-headline text-2xl font-bold text-[var(--color-on-surface)]">Confirmar contraseña</h1>
        <p class="text-sm text-[var(--color-on-surface-variant)] mt-1 leading-relaxed">
            {{ __('Esta es una área segura. Por favor confirma tu contraseña antes de continuar.') }}
        </p>
    </div>

    <form wire:submit="confirmPassword" class="space-y-5">

        {{-- Password --}}
        <div>
            <x-input-label for="password" :value="__('Contraseña')" />
            <x-text-input
                wire:model="password"
                id="password"
                class="block w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
            />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        {{-- Submit --}}
        <x-primary-button class="w-full">
            {{ __('Confirmar') }}
        </x-primary-button>

    </form>

</div>
