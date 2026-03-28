<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CreditCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CreditCheckoutService $checkout,
    ) {}

    public function create(Request $request, string $plan): RedirectResponse
    {
        $validPlans = array_keys((array) config('credits.prices'));

        $request->validate([
            'plan' => 'in:'.implode(',', $validPlans),
        ]);

        if (! in_array($plan, $validPlans, strict: true)) {
            abort(422, 'Invalid plan.');
        }

        /** @var User $user */
        $user = $request->user();

        $dto = $this->checkout->createCheckoutSession($user, $plan);

        return redirect()->away($dto->checkoutUrl);
    }

    public function success(Request $request): View
    {
        return view('checkout.success');
    }

    public function cancel(Request $request): View
    {
        return view('checkout.cancel');
    }
}
