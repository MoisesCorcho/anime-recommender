<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Animes\BuildSemanticTextForEmbeddingAction;
use App\Models\Anime;
use App\Services\Ai\AnimeEmbeddingService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Facades\DB;

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
     * Orchestrates three steps: build semantic texts, generate embeddings
     * via a single API call, then persist all vectors inside a transaction.
     */
    public function handle(
        BuildSemanticTextForEmbeddingAction $action,
        AnimeEmbeddingService $embeddingService,
    ): void {
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

        $dtos = $animes->map(fn (Anime $anime) => $action->execute($anime))->all();

        /** @var array<string, list<float>> $vectors */
        $vectors = $embeddingService->generate($dtos);

        $now = now()->toDateTimeString();

        DB::transaction(function () use ($vectors, $now): void {
            foreach ($vectors as $animeId => $vector) {
                DB::table('animes')
                    ->where('id', $animeId)
                    ->update([
                        'embedding' => json_encode($vector),
                        'updated_at' => $now,
                    ]);
            }
        });
    }
}
