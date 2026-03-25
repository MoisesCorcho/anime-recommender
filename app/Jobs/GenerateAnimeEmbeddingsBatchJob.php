<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Anime;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Embeddings;

#[Tries(3)]
#[Backoff(30, 60, 120)]
class GenerateAnimeEmbeddingsBatchJob implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * @param  list<string>  $animeIds  ULIDs of the animes to embed in this batch.
     */
    public function __construct(
        public readonly array $animeIds,
    ) {}

    /**
     * Execute the job.
     *
     * Generates embeddings for all animes in the chunk with a single API call,
     * then persists each vector directly via query builder to bypass model events.
     */
    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $animes = Anime::query()
            ->select(['id', 'title', 'description', 'genres', 'type'])
            ->whereIn('id', $this->animeIds)
            ->get();

        if ($animes->isEmpty()) {
            return;
        }

        /** @var list<string> $texts */
        $texts = $animes->map(fn (Anime $anime): string => $this->buildEmbeddingText($anime))->all();

        $response = Embeddings::for($texts)
            ->dimensions(1536)
            ->generate();

        $now = now()->toDateTimeString();

        DB::transaction(function () use ($animes, $response, $now): void {
            foreach ($animes as $index => $anime) {
                /** @var list<float> $vector */
                $vector = $response->embeddings[$index];

                DB::table('animes')
                    ->where('id', $anime->id)
                    ->update([
                        'embedding' => json_encode($vector),
                        'updated_at' => $now,
                    ]);
            }
        });
    }

    /**
     * Build the semantically rich source text to embed for a given anime.
     *
     * Injecting structured labels (Format, Genres, Synopsis) alongside the
     * title gives the embedding model explicit semantic anchors, enabling
     * better vector clustering by format and genre in the latent space.
     * Without these labels, two action TV series and a drama movie could end
     * up with similar distances purely based on description vocabulary.
     */
    private function buildEmbeddingText(Anime $anime): string
    {
        // Bullet-proof genre handling: the model cast returns an array, but
        // raw DB reads (or partial hydration) can still return a JSON string.
        $genresText = '';

        if (is_array($anime->genres)) {
            $genresText = implode(', ', $anime->genres);
        } elseif (is_string($anime->genres)) {
            $decoded = json_decode($anime->genres, true);
            $genresText = is_array($decoded) ? implode(', ', $decoded) : $anime->genres;
        }

        $text = "Title: {$anime->title}. ";

        if ($anime->type) {
            $text .= "Format: {$anime->type}. ";
        }

        if ($genresText) {
            $text .= "Genres: {$genresText}. ";
        }

        if ($anime->description) {
            $text .= "Synopsis: {$anime->description}";
        }

        return trim($text);
    }
}
