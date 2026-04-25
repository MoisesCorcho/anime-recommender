<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\DataTransferObjects\AnimeEmbeddingTextDTO;
use Laravel\Ai\Embeddings;

/**
 * Encapsulates the embedding API call for a batch of anime texts.
 *
 * Accepts a list of DTOs (each carrying an anime ID and its pre-built
 * semantic text), sends all texts in a single API call, and returns a
 * map of animeId → embedding vector — preserving the identity link
 * without exposing SDK internals to the Job.
 */
final class AnimeEmbeddingService
{
    /**
     * Generate 1536-dim embeddings for the given anime texts.
     *
     * @param  list<AnimeEmbeddingTextDTO>  $dtos
     * @return array<string, list<float>> Map of animeId => embedding vector.
     */
    public function generate(array $dtos): array
    {
        $texts = array_map(
            fn (AnimeEmbeddingTextDTO $dto): string => $dto->text,
            $dtos,
        );

        $response = Embeddings::for($texts)
            ->dimensions(1536)
            ->generate();

        $result = [];

        foreach ($dtos as $index => $dto) {
            /** @var list<float> $vector */
            $vector = $response->embeddings[$index];
            $result[$dto->animeId] = $vector;
        }

        return $result;
    }
}
