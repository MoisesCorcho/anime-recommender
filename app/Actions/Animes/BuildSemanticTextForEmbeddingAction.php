<?php

declare(strict_types=1);

namespace App\Actions\Animes;

use App\DataTransferObjects\AnimeEmbeddingTextDTO;
use App\Models\Anime;

/**
 * Builds a semantically rich source text from an Anime model,
 * ready to be sent to the embedding API.
 *
 * Injecting structured labels (Format, Genres, Synopsis) alongside the
 * title gives the embedding model explicit semantic anchors, enabling
 * better vector clustering by format and genre in the latent space.
 * Without these labels, two action TV series and a drama movie could end
 * up with similar distances purely based on description vocabulary.
 */
final class BuildSemanticTextForEmbeddingAction
{
    public function execute(Anime $anime): AnimeEmbeddingTextDTO
    {
        $text = "Title: {$anime->title}. ";

        if ($anime->type) {
            $text .= "Format: {$anime->type}. ";
        }

        $genresText = $this->resolveGenresText($anime->genres);

        if ($genresText !== '') {
            $text .= "Genres: {$genresText}. ";
        }

        if ($anime->description) {
            $text .= "Synopsis: {$anime->description}";
        }

        return new AnimeEmbeddingTextDTO(
            animeId: $anime->id,
            text: trim($text),
        );
    }

    /**
     * Normalises the genres value into a comma-separated string.
     *
     * The Eloquent cast returns an array, but raw DB reads or partial
     * hydration (e.g. select with no cast) can still return a JSON string.
     */
    private function resolveGenresText(mixed $genres): string
    {
        if (is_array($genres)) {
            return implode(', ', $genres);
        }

        if (is_string($genres) && $genres !== '') {
            $decoded = json_decode($genres, true);

            return is_array($decoded) ? implode(', ', $decoded) : $genres;
        }

        return '';
    }
}
