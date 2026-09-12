<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_order_id',
        'tenant_id',
        'menu_id',
        'name_snapshot',
        'price_snapshot',
        'prep_minutes_snapshot',
        'quantity',
        'modifier_total',
        'line_total',
    ];

    public function tenantOrder(): BelongsTo
    {
        return $this->belongsTo(TenantOrder::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(OrderItemModifier::class);
    }
}