<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));
            return;
        }

        $this->reset('email');
        session()->flash('status', __($status));
    }
}; ?>

<div>

    {{-- Heading --}}
    <div class="mb-6">
        <h1 class="font-headline text-2xl font-bold text-[var(--color-on-surface)]">Recuperar contraseña</h1>
        <p class="text-sm text-[var(--color-on-surface-variant)] mt-1 leading-relaxed">
            {{ __('Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.') }}
        </p>
    </div>

    {{-- Session Status --}}
    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="space-y-5">

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
                autofocus
                placeholder="tu@email.com"
            />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        {{-- Submit --}}
        <x-primary-button class="w-full">
            {{ __('Enviar enlace de recuperación') }}
        </x-primary-button>

    </form>

    {{-- Back to login --}}
    <p class="text-center text-sm text-[var(--color-on-surface-variant)] pt-4">
        <a
            href="{{ route('login') }}"
            wire:navigate
            class="font-medium text-[var(--color-primary)] hover:text-[var(--color-primary-dim)] transition-colors duration-150 inline-flex items-center gap-1"
        >
            <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
            {{ __('Volver al inicio de sesión') }}
        </a>
    </p>

</div>
