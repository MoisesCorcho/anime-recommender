<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AnimeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'mal_id',
    'title',
    'description',
    'image_url',
    'type',
    'episodes',
    'status',
    'released_year',
    'genres',
    'score',
    'embedding',
])]
class Anime extends Model
{
    /** @use HasFactory<AnimeFactory> */
    use HasFactory, HasUlids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mal_id' => 'integer',
            'episodes' => 'integer',
            'released_year' => 'integer',
            'genres' => 'array',
            'score' => 'decimal:2',
            'embedding' => 'array',
        ];
    }
}
