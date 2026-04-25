<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CreditTransactionReason;
use App\Enums\CreditTransactionType;
use Database\Factories\CreditTransactionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    /** @use HasFactory<CreditTransactionFactory> */
    use HasFactory, HasUlids;

    /**
     * Immutable ledger — no updated_at column.
     */
    public $timestamps = false;

    const CREATED_AT = 'created_at';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'reason',
        'reference_id',
        'balance_after',
        'created_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'type' => CreditTransactionType::class,
            'reason' => CreditTransactionReason::class,
            'amount' => 'integer',
            'balance_after' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
