<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Facades\DB;

#[Tries(3)]
#[Backoff([30, 60, 120])]
class UpdateUserPreferenceVectorJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $userId,
        public readonly string $animeId,
        public readonly ?int $score,
    ) {}

    /**
     * Execute the job.
     *
     * Reads the anime's stored embedding, applies the Exponential Moving Average (EMA)
     * formula against the user's current preference_vector, and persists the result.
     * No external AI API calls are made — all vectors are read from the database.
     */
    public function handle(): void
    {
        $embeddingRaw = DB::table('animes')
            ->where('id', $this->animeId)
            ->value('embedding');

        if ($embeddingRaw === null) {
            return;
        }

        /** @var list<float> $animeVector */
        $animeVector = json_decode($embeddingRaw, true);

        $existingRaw = DB::table('users')
            ->where('id', $this->userId)
            ->value('preference_vector');

        $oldVector = $existingRaw !== null
            ? json_decode($existingRaw, true)
            : null;

        $newVector = $this->calculateEma($oldVector, $animeVector, $this->score);

        DB::table('users')
            ->where('id', $this->userId)
            ->update([
                'preference_vector' => json_encode($newVector),
                'updated_at' => now()->toDateTimeString(),
            ]);
    }

    /**
     * Compute the new preference vector using an Exponential Moving Average.
     *
     * Cold start (null old vector): absorb the anime vector at full score weight.
     * Existing vector: blend old history (α) with the new anime signal (1 − α),
     * scaled by the normalised score weight (score / 10, or 1.0 if unrated).
     *
     * @param  list<float>|null  $oldVector
     * @param  list<float>  $animeVector
     * @return list<float>
     */
    private function calculateEma(?array $oldVector, array $animeVector, ?int $score): array
    {
        $alpha = (float) config('recommendations.ema_alpha', 0.8);
        $scoreWeight = $score !== null ? ($score / 10) : 1.0;
        $dims = count($animeVector);

        if ($oldVector === null) {
            return array_map(
                fn (float $v): float => $v * $scoreWeight,
                $animeVector,
            );
        }

        $result = [];

        for ($i = 0; $i < $dims; $i++) {
            $result[$i] = ($oldVector[$i] * $alpha)
                + ($animeVector[$i] * (1 - $alpha) * $scoreWeight);
        }

        return $result;
    }
}
