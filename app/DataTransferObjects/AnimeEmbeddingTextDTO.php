<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Immutable pair of anime ULID and its pre-built semantic text.
 *
 * Decouples the text-building logic (Action) from the embedding
 * API call (Service), keeping each class focused on a single concern.
 */
final readonly class AnimeEmbeddingTextDTO
{
    /**
     * @param  string  $animeId  ULID of the anime record.
     * @param  string  $text  Semantically rich source text ready for embedding.
     */
    public function __construct(
        public readonly string $animeId,
        public readonly string $text,
    ) {}
}
