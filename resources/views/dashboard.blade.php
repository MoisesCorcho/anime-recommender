<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                    
                    <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Área de Testing (Temporal)</h3>
                        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Botón para probar la pasarela de pagos directo a Stripe.</p>
                        
                        <form action="{{ route('checkout.create', 'pro') }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow-md transition-colors">
                                💳 Testear Stripe Checkout (Plan Pro)
                            </button>
                        </form>

                        <div class="mt-4 flex flex-wrap gap-3">
                            @foreach (['pack_100' => '100 créditos', 'pack_500' => '500 créditos', 'pack_1000' => '1000 créditos'] as $pack => $label)
                                <form action="{{ route('checkout.create', $pack) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded shadow-md transition-colors">
                                        🪙 Comprar {{ $label }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
