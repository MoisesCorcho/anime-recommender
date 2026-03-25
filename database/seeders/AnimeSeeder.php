<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\DataTransferObjects\CreateAnimeDTO;
use App\Models\Anime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use SplFileObject;

class AnimeSeeder extends Seeder
{
    private const int CHUNK_SIZE = 500;

    private const string CSV_PATH = 'database/data/anime-dataset-2025.csv';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = base_path(self::CSV_PATH);

        $file = new SplFileObject($path, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        /** @var list<string> $headers */
        $headers = (array) $file->current();
        $headerMap = array_flip($headers);
        $file->next();

        $chunk = [];
        $totalImported = 0;
        $totalSkipped = 0;

        $this->command->info('Importing anime dataset...');

        while (! $file->eof()) {
            /** @var list<string>|false $row */
            $row = $file->current();
            $file->next();

            if (! is_array($row) || count($row) < count($headers)) {
                $totalSkipped++;

                continue;
            }

            $dto = $this->parseCsvRow($row, $headerMap);

            if ($dto === null) {
                $totalSkipped++;

                continue;
            }

            $chunk[] = $this->dtoToAttributes($dto);

            if (count($chunk) >= self::CHUNK_SIZE) {
                $this->upsertChunk($chunk);
                $totalImported += count($chunk);
                $chunk = [];
                $this->command->info("  → {$totalImported} records imported...");
            }
        }

        if ($chunk !== []) {
            $this->upsertChunk($chunk);
            $totalImported += count($chunk);
        }

        $this->command->info("Done. Imported: {$totalImported} | Skipped: {$totalSkipped}");
    }

    /**
     * Parse a raw CSV row into a typed DTO.
     *
     * Returns null when the row is invalid and should be skipped.
     *
     * @param  list<string>  $row
     * @param  array<string,int>  $headerMap
     */
    private function parseCsvRow(array $row, array $headerMap): ?CreateAnimeDTO
    {
        $malId = (int) ($row[$headerMap['myanimelist_id']] ?? 0);

        if ($malId <= 0) {
            return null;
        }

        $title = trim($row[$headerMap['title']] ?? '');

        if ($title === '') {
            return null;
        }

        $episodesRaw = $row[$headerMap['Episodes']] ?? '';
        $yearRaw = $row[$headerMap['Released_Year']] ?? '';
        $scoreRaw = $row[$headerMap['Score']] ?? '';

        return new CreateAnimeDTO(
            malId: $malId,
            title: $title,
            description: $this->nullableString($row[$headerMap['description']] ?? ''),
            imageUrl: $this->nullableString($row[$headerMap['image']] ?? ''),
            type: $this->nullableString($row[$headerMap['Type']] ?? ''),
            episodes: is_numeric($episodesRaw) ? (int) (float) $episodesRaw : null,
            status: $this->nullableString($row[$headerMap['Status']] ?? ''),
            releasedYear: is_numeric($yearRaw) ? (int) (float) $yearRaw : null,
            genres: $this->parseGenres($row[$headerMap['Genres']] ?? ''),
            score: is_numeric($scoreRaw) ? (float) $scoreRaw : null,
        );
    }

    /**
     * Convert a DTO into a flat attribute array suitable for bulk upsert.
     *
     * ULIDs are generated here because mass-insert operations bypass
     * Eloquent model events (where HasUlids would normally generate them).
     *
     * @return array<string, mixed>
     */
    private function dtoToAttributes(CreateAnimeDTO $dto): array
    {
        $now = now()->toDateTimeString();

        return [
            'id' => (string) Str::ulid(),
            'mal_id' => $dto->malId,
            'title' => $dto->title,
            'description' => $dto->description,
            'image_url' => $dto->imageUrl,
            'type' => $dto->type,
            'episodes' => $dto->episodes,
            'status' => $dto->status,
            'released_year' => $dto->releasedYear,
            'genres' => json_encode($dto->genres),
            'score' => $dto->score,
            'embedding' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * Persist a chunk using upsert to remain idempotent on re-runs.
     *
     * `id` and `created_at` are intentionally excluded from the update
     * columns to preserve the original record identity and insertion time.
     *
     * @param  list<array<string, mixed>>  $chunk
     */
    private function upsertChunk(array $chunk): void
    {
        Anime::upsert($chunk, uniqueBy: ['mal_id'], update: [
            'title',
            'description',
            'image_url',
            'type',
            'episodes',
            'status',
            'released_year',
            'genres',
            'score',
            'updated_at',
        ]);
    }

    /**
     * Split a comma-separated genres string into a normalized array.
     *
     * @return list<string>
     */
    private function parseGenres(string $raw): array
    {
        if (trim($raw) === '') {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', explode(',', $raw))
        ));
    }

    /**
     * Return null for empty or whitespace-only strings.
     */
    private function nullableString(string $value): ?string
    {
        $trimmed = trim($value);

        return $trimmed !== '' ? $trimmed : null;
    }
}
