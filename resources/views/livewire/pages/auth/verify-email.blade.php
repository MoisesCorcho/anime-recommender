<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>

    {{-- Heading --}}
    <div class="mb-6 text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-[var(--color-primary)]/10 border border-[var(--color-primary)]/20 mb-4">
            <span class="material-symbols-outlined text-[var(--color-primary)]" style="font-size:28px;">mark_email_unread</span>
        </div>
        <h1 class="font-headline text-2xl font-bold text-[var(--color-on-surface)]">Verifica tu correo</h1>
        <p class="text-sm text-[var(--color-on-surface-variant)] mt-2 leading-relaxed max-w-sm mx-auto">
            {{ __('¡Gracias por registrarte! Haz clic en el enlace que enviamos a tu correo electrónico para verificar tu cuenta.') }}
        </p>
    </div>

    {{-- Sent confirmation --}}
    @if (session('status') == 'verification-link-sent')
        <x-auth-session-status class="mb-5" :status="__('Se ha enviado un nuevo enlace de verificación a tu correo.')" />
    @endif

    {{-- Actions --}}
    <div class="flex flex-col gap-3 mt-4">
        <x-primary-button wire:click="sendVerification" class="w-full justify-center">
            {{ __('Reenviar correo de verificación') }}
        </x-primary-button>

        <button
            wire:click="logout"
            type="button"
            class="w-full text-center text-sm text-[var(--color-on-surface-variant)] hover:text-[var(--color-primary)] transition-colors duration-150 py-2"
        >
            {{ __('Cerrar sesión') }}
        </button>
    </div>

</div>
