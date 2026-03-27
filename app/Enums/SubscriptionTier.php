<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriptionTier: string
{
    case Free = 'FREE';
    case Pro = 'PRO';

    /**
     * Human-readable label for display in UI or logs.
     */
    public function label(): string
    {
        return __("enums.subscription_tier.{$this->value}");
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
