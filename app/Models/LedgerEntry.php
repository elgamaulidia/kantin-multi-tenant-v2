<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'order_id',
        'payment_id',
        'withdrawal_id',
        'idempotency_key',
        'type',
        'available_delta',
        'held_delta',
    ];

    protected function casts(): array
    {
        return [
            'available_delta' => 'integer',
            'held_delta' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(Withdrawal::class);
    }
}