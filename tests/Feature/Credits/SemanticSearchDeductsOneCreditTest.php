<?php

declare(strict_types=1);

use App\DataTransferObjects\Credits\CreditDeductionResultDTO;
use App\Enums\CreditTransactionReason;
use App\Enums\CreditTransactionType;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('deducts one credit and records a transaction', function (): void {
    $user = User::factory()->create(['credit_balance' => 5]);

    $service = app(CreditService::class);
    $result = $service->deductForSemanticSearch($user);

    expect($result)->toBeInstanceOf(CreditDeductionResultDTO::class)
        ->and($result->balanceAfter)->toBe(4);

    expect($user->fresh()->credit_balance)->toBe(4);
    expect($user->creditTransactions()->count())->toBe(1);

    $tx = $user->creditTransactions()->first();
    expect($tx->type)->toBe(CreditTransactionType::Debit)
        ->and($tx->reason)->toBe(CreditTransactionReason::SemanticSearch)
        ->and($tx->amount)->toBe(1)
        ->and($tx->balance_after)->toBe(4);
});
