<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'order_number',
        'canteen_id',
        'customer_session_id',
        'checkout_key',
        'tracking_token_hash',
        'status',
        'total_amount',
        'customer_snapshot',
        'table_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'customer_snapshot' => 'array',
            'table_snapshot' => 'array',
        ];
    }

    public function canteen(): BelongsTo
    {
        return $this->belongsTo(Canteen::class);
    }

    public function customerSession(): BelongsTo
    {
        return $this->belongsTo(CustomerSession::class);
    }

    public function tenantOrders(): HasMany
    {
        return $this->hasMany(TenantOrder::class);
    }
}