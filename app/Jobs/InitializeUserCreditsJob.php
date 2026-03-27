<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\CreditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class InitializeUserCreditsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly User $user,
    ) {}

    public function handle(CreditService $credits): void
    {
        $credits->initializeRegistrationBonus($this->user);
    }
}
