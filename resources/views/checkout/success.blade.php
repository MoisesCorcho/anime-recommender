<x-app-layout>
    <div class="py-24 flex items-center justify-center min-h-[calc(100vh-64px)]">
        <div class="max-w-md w-full px-6 text-center">
            <div class="relative mb-8 flex justify-center">
                {{-- Success Glow Effect --}}
                <div class="absolute inset-0 bg-primary/20 blur-3xl rounded-full scale-150"></div>
                
                <div class="relative w-24 h-24 bg-primary/10 rounded-3xl flex items-center justify-center text-primary border border-primary/20 shadow-[0_0_40px_rgba(var(--color-primary),0.2)]">
                    <span class="material-symbols-outlined text-[48px] animate-pulse">check_circle</span>
                </div>
            </div>

            <h1 class="text-3xl font-headline font-extrabold text-on-surface mb-4">Payment Successful!</h1>
            <p class="text-on-surface-variant mb-8 leading-relaxed">
                Thank you for your purchase. Your credits have been added to your account, and you can now continue exploring the anime world with our AI search.
            </p>

            <div class="space-y-4">
                <a href="{{ route('dashboard') }}" class="block w-full py-4 px-6 bg-primary hover:bg-primary/90 text-on-primary font-bold rounded-2xl transition-all shadow-lg hover:shadow-primary/40 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">dashboard</span>
                    Go to Dashboard
                </a>
                
                <p class="text-[10px] font-bold text-outline-variant uppercase tracking-widest pt-4">
                    Transaction processed securely by Stripe
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
