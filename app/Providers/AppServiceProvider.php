<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\SubscriptionTier;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\Payments\StripePaymentGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            PaymentGatewayInterface::class,
            StripePaymentGateway::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('semantic-search', function (Request $request): Limit {
            $user = $request->user();
            $limit = $user?->subscription_tier === SubscriptionTier::Pro
                ? (int) config('credits.rate_limit_pro', 10)
                : (int) config('credits.rate_limit_free', 5);

            return Limit::perMinute($limit)->by($user?->id ?? $request->ip());
        });
    }
}
