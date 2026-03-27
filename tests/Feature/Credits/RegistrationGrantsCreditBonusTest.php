<?php

declare(strict_types=1);

use App\Enums\CreditTransactionReason;
use App\Jobs\InitializeUserCreditsJob;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('dispatches InitializeUserCreditsJob when a user registers', function (): void {
    Queue::fake();

    $user = User::factory()->create();

    event(new Registered($user));

    Queue::assertPushed(InitializeUserCreditsJob::class, function (InitializeUserCreditsJob $job) use ($user): bool {
        return $job->user->id === $user->id;
    });
});

it('grants registration bonus credits when job is executed', function (): void {
    $user = User::factory()->create(['credit_balance' => 0]);

    $bonus = (int) config('credits.registration_bonus', 50);

    (new InitializeUserCreditsJob($user))->handle(app(CreditService::class));

    expect($user->fresh()->credit_balance)->toBe($bonus);
    expect($user->creditTransactions()->count())->toBe(1);
    expect($user->creditTransactions()->first()->reason)
        ->toBe(CreditTransactionReason::RegistrationBonus);
});
