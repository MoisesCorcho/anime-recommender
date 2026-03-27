<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Jobs\InitializeUserCreditsJob;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class GrantRegistrationCreditsListener
{
    public function handle(Registered $event): void
    {
        if ($event->user instanceof User) {
            InitializeUserCreditsJob::dispatch($event->user);
        }
    }
}
