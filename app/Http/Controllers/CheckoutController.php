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
    private const VALID_PLANS = ['pro', 'pack_100', 'pack_500', 'pack_1000'];

    public function __construct(
        private readonly CreditCheckoutService $checkout,
    ) {}

    public function create(Request $request, string $plan): RedirectResponse
    {
        $request->validate([
            'plan' => 'in:pro,pack_100,pack_500,pack_1000',
        ]);

        if (! in_array($plan, self::VALID_PLANS, strict: true)) {
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
