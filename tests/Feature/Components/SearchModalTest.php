<?php

use App\DataTransferObjects\Payments\CheckoutSessionDTO;
use App\Models\User;
use App\Services\Payments\PaymentGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Livewire\Volt\Volt;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

it('deducts credits when performing a search', function () {
    $user = User::factory()->create(['credit_balance' => 10]);
    $this->actingAs($user);

    Volt::test('components.search-modal')
        ->set('search', 'samurai')
        ->call('performSearch')
        ->assertSet('showUpgradeModal', false)
        ->assertHasNoErrors();

    expect($user->fresh()->credit_balance)->toBe(9);
});

it('shows upgrade modal when credits are insufficient', function () {
    $user = User::factory()->create(['credit_balance' => 0]);
    $this->actingAs($user);

    Volt::test('components.search-modal')
        ->set('search', 'samurai')
        ->call('performSearch')
        ->assertSet('showUpgradeModal', true)
        ->assertHasNoErrors();

    expect($user->fresh()->credit_balance)->toBe(0);
});

it('redirects to checkout when buying credits', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_123']);
    $this->actingAs($user);

    $checkoutUrl = 'https://checkout.stripe.com/test';

    // Configurar el precio para que no sea vacío
    config(['credits.prices.pro' => 'price_123']);

    $mockGateway = mock(PaymentGatewayInterface::class);
    $mockGateway->shouldReceive('createCheckoutSession')
        ->once()
        ->andReturn(new CheckoutSessionDTO(
            sessionId: 'sess_123',
            checkoutUrl: $checkoutUrl
        ));

    $this->instance(PaymentGatewayInterface::class, $mockGateway);

    Volt::test('components.search-modal')
        ->call('checkout', 'pro')
        ->assertRedirect($checkoutUrl);
});

it('can perform a search from recent searches and deducts credits', function () {
    $user = User::factory()->create(['credit_balance' => 5]);
    Session::put('recent_searches', ['samurai']);
    $this->actingAs($user);

    Volt::test('components.search-modal')
        ->set('showModal', true)
        ->call('selectSearch', 'samurai')
        ->assertSet('search', 'samurai')
        ->assertHasNoErrors();

    expect($user->fresh()->credit_balance)->toBe(4);
});
