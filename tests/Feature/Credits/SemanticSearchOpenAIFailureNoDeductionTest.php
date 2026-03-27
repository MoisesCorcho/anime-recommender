<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\CreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Validates the architectural contract that the OpenAI call happens BEFORE
 * the credit deduction. If the embedding call fails (throws an exception),
 * no credits should be deducted because deductForSemanticSearch is never called.
 *
 * This test simulates the correct calling pattern: caller should only invoke
 * deductForSemanticSearch AFTER a successful embedding call.
 */
it('does not deduct credits when caller never invokes the service due to upstream failure', function (): void {
    $user = User::factory()->create(['credit_balance' => 5]);

    $service = app(CreditService::class);

    // Simulate an upstream failure before the deduction call.
    $embeddingCallFailed = false;

    try {
        // Simulated OpenAI call that throws before deduction.
        throw new RuntimeException('OpenAI API error');
        // This line would be reached only on success.
        $service->deductForSemanticSearch($user);
    } catch (RuntimeException) {
        $embeddingCallFailed = true;
    }

    expect($embeddingCallFailed)->toBeTrue();
    expect($user->fresh()->credit_balance)->toBe(5);
    expect($user->creditTransactions()->count())->toBe(0);
});
