<x-app-layout>
    <div class="py-24 flex items-center justify-center min-h-[calc(100vh-64px)]">
        <div class="max-w-md w-full px-6 text-center">
            <div class="relative mb-8 flex justify-center">
                {{-- Cancel Glow Effect --}}
                <div class="absolute inset-0 bg-error/20 blur-3xl rounded-full scale-150"></div>
                
                <div class="relative w-24 h-24 bg-surface-container rounded-3xl flex items-center justify-center text-error border border-error/20 shadow-[0_0_40px_rgba(var(--color-error),0.2)]">
                    <span class="material-symbols-outlined text-[48px]">cancel</span>
                </div>
            </div>

            <h1 class="text-3xl font-headline font-extrabold text-on-surface mb-4">Payment Canceled</h1>
            <p class="text-on-surface-variant mb-8 leading-relaxed">
                No worries! Your order has been canceled. If you ran into any issues or have questions about our plans, feel free to try again or reach out to us.
            </p>

            <div class="space-y-4">
                <a href="{{ route('dashboard') }}" class="block w-full py-4 px-6 bg-surface-variant hover:bg-surface-container-highest text-on-surface-variant hover:text-white font-bold rounded-2xl transition-all border border-outline-variant/20 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                    Return to Dashboard
                </a>
                
                <p class="text-[10px] font-bold text-outline-variant uppercase tracking-widest pt-4">
                    Your account has not been charged
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
