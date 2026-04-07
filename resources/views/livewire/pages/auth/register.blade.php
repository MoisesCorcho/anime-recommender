<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>

    {{-- Heading --}}
    <div class="mb-6">
        <h1 class="font-headline text-2xl font-bold text-[var(--color-on-surface)]">Crear cuenta</h1>
        <p class="text-sm text-[var(--color-on-surface-variant)] mt-1">Únete y descubre tu próximo anime favorito.</p>
    </div>

    <form wire:submit="register" class="space-y-5">

        {{-- Name --}}
        <div>
            <x-input-label for="name" :value="__('Nombre')" />
            <x-text-input
                wire:model="name"
                id="name"
                class="block w-full"
                type="text"
                name="name"
                required
                autofocus
                autocomplete="name"
                placeholder="Tu nombre"
            />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        {{-- Email --}}
        <div>
            <x-input-label for="email" :value="__('Correo electrónico')" />
            <x-text-input
                wire:model="email"
                id="email"
                class="block w-full"
                type="email"
                name="email"
                required
                autocomplete="username"
                placeholder="tu@email.com"
            />
            <x-input-error :messages="$errors->get('email')" />
        </div>

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
                autocomplete="new-password"
                placeholder="Mínimo 8 caracteres"
            />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        {{-- Confirm Password --}}
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" />
            <x-text-input
                wire:model="password_confirmation"
                id="password_confirmation"
                class="block w-full"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Repite tu contraseña"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        {{-- Submit --}}
        <x-primary-button class="w-full mt-2">
            {{ __('Crear cuenta') }}
        </x-primary-button>

    </form>

    {{-- Login link --}}
    <p class="text-center text-sm text-[var(--color-on-surface-variant)] pt-4">
        {{ __('¿Ya tienes cuenta?') }}
        <a
            href="{{ route('login') }}"
            wire:navigate
            class="font-medium text-[var(--color-primary)] hover:text-[var(--color-primary-dim)] transition-colors duration-150"
        >
            {{ __('Inicia sesión') }}
        </a>
    </p>

</div>
