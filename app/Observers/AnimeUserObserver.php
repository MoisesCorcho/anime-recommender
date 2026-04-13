<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\UserAnimeStatus;
use App\Jobs\UpdateUserPreferenceVectorJob;
use App\Models\AnimeUser;

class AnimeUserObserver
{
    /**
     * Handle the AnimeUser "created" event.
     *
     * Triggers when a user first attaches an anime to their list. Dispatches
     * the preference vector update if the initial state already carries an
     * explicit signal (completed, favourite, or scored).
     */
    public function created(AnimeUser $animeUser): void
    {
        if (
            $animeUser->status === UserAnimeStatus::Completed
            || $animeUser->is_favorite === true
            || $animeUser->score !== null
        ) {
            $this->dispatch($animeUser);
        }
    }

    /**
     * Handle the AnimeUser "updated" event.
     *
     * Triggers only when a column that carries explicit user intent changes to
     * a meaningful value. Uses wasChanged() — reliable post-DB-write.
     */
    public function updated(AnimeUser $animeUser): void
    {
        if ($animeUser->wasChanged('status') && $animeUser->status === UserAnimeStatus::Completed) {
            $this->dispatch($animeUser);

            return;
        }

        if ($animeUser->wasChanged('is_favorite') && $animeUser->is_favorite === true) {
            $this->dispatch($animeUser);

            return;
        }

        if ($animeUser->wasChanged('score') && $animeUser->score !== null) {
            $this->dispatch($animeUser);
        }
    }

    private function dispatch(AnimeUser $animeUser): void
    {
        UpdateUserPreferenceVectorJob::dispatch(
            userId: $animeUser->user_id,
            animeId: $animeUser->anime_id,
            score: $animeUser->score,
        );
    }
}
