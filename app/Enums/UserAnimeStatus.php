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

    /**
     * Human-readable label for display in UI or logs.
     */
    public function label(): string
    {
        return __("enums.user_anime_status.{$this->value}");
    }

    /**
     * All cases as a value => label map, ready for select inputs.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_column(
            array_map(
                fn (self $case): array => ['value' => $case->value, 'label' => $case->label()],
                self::cases(),
            ),
            'label',
            'value',
        );
    }
}
