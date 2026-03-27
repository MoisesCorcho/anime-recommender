<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\StripeWebhookEventFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeWebhookEvent extends Model
{
    /** @use HasFactory<StripeWebhookEventFactory> */
    use HasFactory, HasUlids;

    /**
     * Immutable ledger — no updated_at column.
     */
    public $timestamps = false;

    const CREATED_AT = 'created_at';

    /** @var list<string> */
    protected $fillable = [
        'stripe_event_id',
        'event_type',
        'payload',
        'processed_at',
        'created_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}
