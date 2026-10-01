<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantOrder extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'order_id',
        'commission_id',
        'status',
        'scheduled_at',
        'subtotal_amount',
        'tax_amount',
        'fee_amount',
        'commission_amount',
        'net_amount',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'subtotal_amount' => 'integer',
            'tax_amount' => 'integer',
            'fee_amount' => 'integer',
            'commission_amount' => 'integer',
            'net_amount' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function commissionScheme(): BelongsTo
    {
        return $this->belongsTo(CommissionScheme::class, 'commission_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}