<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\GenerateAnimeEmbeddingsBatchJob;
use App\Models\Anime;
use Illuminate\Bus\Batch;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Throwable;

#[Signature('anime:generate-embeddings {--fresh : Re-generate embeddings for animes that already have one} {--chunk=100 : Number of animes per job (each job = 1 API call)}')]
#[Description('Generate and store 1536-dim embeddings for all animes that lack one.')]
class GenerateAnimeEmbeddingsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $fresh = (bool) $this->option('fresh');
        $chunkSize = max(1, (int) $this->option('chunk'));

        $query = Anime::query()->select('id');

        if (! $fresh) {
            $query->whereNull('embedding');
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info('All animes already have embeddings. Use --fresh to regenerate.');

            return self::SUCCESS;
        }

        $this->info("Dispatching jobs for {$total} animes in chunks of {$chunkSize}...");

        /** @var list<GenerateAnimeEmbeddingsBatchJob> $jobs */
        $jobs = [];

        $query->lazyById(1000)->chunk($chunkSize, function ($chunk) use (&$jobs): void {
            $jobs[] = new GenerateAnimeEmbeddingsBatchJob(
                $chunk->pluck('id')->all()
            );
        });

        $batch = Bus::batch($jobs)
            ->name('anime-embeddings')
            ->allowFailures()
            ->then(function (Batch $batch): void {
                logger()->info("Embedding batch [{$batch->id}] completed successfully.", [
                    'total' => $batch->totalJobs,
                    'processed' => $batch->processedJobs(),
                    'failed' => $batch->failedJobs,
                ]);
            })
            ->catch(function (Batch $batch, Throwable $e): void {
                logger()->error("Embedding batch [{$batch->id}] encountered a failure.", [
                    'exception' => $e->getMessage(),
                    'failed' => $batch->failedJobs,
                ]);
            })
            ->dispatch();

        $this->info("Batch [{$batch->id}] dispatched.");
        $this->info("Total jobs: {$batch->totalJobs}");
        $this->line('');
        $this->line('Run the queue worker to start processing:');
        $this->line('  vendor/bin/sail artisan queue:work');

        return self::SUCCESS;
    }
}
