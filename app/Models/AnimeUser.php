<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserAnimeStatus;
use App\Observers\AnimeUserObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\Pivot;
use InvalidArgumentException;

/**
 * Pivot model for the anime_user table.
 *
 * Manages the full tracking relationship between a User and an Anime,
 * including watching status, personal score, episodes seen, and favourites.
 * Extends Pivot (not Model) so that Eloquent treats it as an intermediary
 * and fires the correct lifecycle events for Observer hooks.
 *
 * @property int $user_id
 * @property string $anime_id
 * @property UserAnimeStatus $status
 * @property bool $is_favorite
 * @property int|null $score
 * @property int $episodes_watched
 */
#[ObservedBy([AnimeUserObserver::class])]
#[Fillable(['user_id', 'anime_id', 'status', 'is_favorite', 'score', 'episodes_watched'])]
class AnimeUser extends Pivot
{
    /**
     * The table associated with the model.
     */
    public $table = 'anime_user';

    /**
     * Indicates if the IDs are auto-incrementing.
     * Pivot tables use composite keys, not a single auto-increment column.
     */
    public $incrementing = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => UserAnimeStatus::class,
            'is_favorite' => 'boolean',
            'score' => 'integer',
            'episodes_watched' => 'integer',
        ];
    }

    /**
     * The score attribute mutator.
     *
     * Enforces the domain rule that a personal rating must be between 1 and 10.
     * A null score means the user has not rated the anime yet.
     *
     * @throws InvalidArgumentException when the score is outside the 1–10 range.
     */
    protected function score(): Attribute
    {
        return Attribute::make(
            set: static function (?int $value): ?int {
                if ($value === null) {
                    return null;
                }

                if ($value < 1 || $value > 10) {
                    throw new InvalidArgumentException(
                        "Score must be between 1 and 10, got {$value}."
                    );
                }

                return $value;
            },
        );
    }
}
