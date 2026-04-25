<?php

declare(strict_types=1);

use App\Actions\Animes\BuildSemanticTextForEmbeddingAction;
use App\DataTransferObjects\AnimeEmbeddingTextDTO;
use App\Models\Anime;

function makeAnime(array $attributes): Anime
{
    $anime = new Anime;

    foreach ($attributes as $key => $value) {
        $anime->$key = $value;
    }

    return $anime;
}

it('builds the full semantic text when all fields are present', function () {
    $anime = makeAnime([
        'id' => '01JKWZ1234567890ABCDEF',
        'title' => 'Attack on Titan',
        'type' => 'TV',
        'genres' => ['Action', 'Drama', 'Fantasy'],
        'description' => 'Humanity fights for survival against giant humanoid creatures.',
    ]);

    $dto = (new BuildSemanticTextForEmbeddingAction)->execute($anime);

    expect($dto)->toBeInstanceOf(AnimeEmbeddingTextDTO::class)
        ->and($dto->animeId)->toBe('01JKWZ1234567890ABCDEF')
        ->and($dto->text)->toBe(
            'Title: Attack on Titan. Format: TV. Genres: Action, Drama, Fantasy. Synopsis: Humanity fights for survival against giant humanoid creatures.'
        );
});

it('omits the Format segment when type is null', function () {
    $anime = makeAnime([
        'id' => '01JKWZ0000000000000001',
        'title' => 'Berserk',
        'type' => null,
        'genres' => ['Action', 'Adventure'],
        'description' => 'A mercenary travels a dark medieval world.',
    ]);

    $dto = (new BuildSemanticTextForEmbeddingAction)->execute($anime);

    expect($dto->text)->toBe(
        'Title: Berserk. Genres: Action, Adventure. Synopsis: A mercenary travels a dark medieval world.'
    );
});

it('omits the Genres segment when genres array is empty', function () {
    $anime = makeAnime([
        'id' => '01JKWZ0000000000000002',
        'title' => 'Steins;Gate',
        'type' => 'TV',
        'genres' => [],
        'description' => 'A self-proclaimed mad scientist discovers time travel.',
    ]);

    $dto = (new BuildSemanticTextForEmbeddingAction)->execute($anime);

    expect($dto->text)->toBe(
        'Title: Steins;Gate. Format: TV. Synopsis: A self-proclaimed mad scientist discovers time travel.'
    );
});

it('omits the Synopsis segment when description is null', function () {
    $anime = makeAnime([
        'id' => '01JKWZ0000000000000003',
        'title' => 'Cowboy Bebop',
        'type' => 'TV',
        'genres' => ['Action', 'Sci-Fi'],
        'description' => null,
    ]);

    $dto = (new BuildSemanticTextForEmbeddingAction)->execute($anime);

    expect($dto->text)->toBe(
        'Title: Cowboy Bebop. Format: TV. Genres: Action, Sci-Fi.'
    );
});

it('handles genres as a raw JSON string (partial hydration edge case)', function () {
    $anime = makeAnime([
        'id' => '01JKWZ0000000000000004',
        'title' => 'Fullmetal Alchemist',
        'type' => 'TV',
        'genres' => '["Action","Adventure","Drama"]',
        'description' => 'Two brothers search for the Philosopher\'s Stone.',
    ]);

    $dto = (new BuildSemanticTextForEmbeddingAction)->execute($anime);

    expect($dto->text)->toBe(
        'Title: Fullmetal Alchemist. Format: TV. Genres: Action, Adventure, Drama. Synopsis: Two brothers search for the Philosopher\'s Stone.'
    );
});

it('builds a title-only text when all optional fields are absent', function () {
    $anime = makeAnime([
        'id' => '01JKWZ0000000000000005',
        'title' => 'Unknown Anime',
        'type' => null,
        'genres' => [],
        'description' => null,
    ]);

    $dto = (new BuildSemanticTextForEmbeddingAction)->execute($anime);

    expect($dto->text)->toBe('Title: Unknown Anime.');
});
