<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\InteractionType;
use App\Models\User;
use App\Models\UserInteraction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LogUserInteractionJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ?User $user,
        public readonly InteractionType $type,
        public readonly array $payload,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        UserInteraction::create([
            'user_id' => $this->user?->id,
            'type' => $this->type,
            'payload' => $this->payload,
        ]);
    }
}
