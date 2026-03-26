<?php

declare(strict_types=1);

namespace App\Enums;

enum UserAnimeStatus: string
{
    case Watching = 'WATCHING';
    case Completed = 'COMPLETED';
    case OnHold = 'ON_HOLD';
    case Dropped = 'DROPPED';
    case PlanToWatch = 'PLAN_TO_WATCH';
    case Blacklisted = 'BLACKLISTED';
}
