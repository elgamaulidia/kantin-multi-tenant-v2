<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionScheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'commission_rate',
        'valid_from',
        'valid_to',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'valid_from' => 'immutable_datetime',
            'valid_to' => 'immutable_datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tenantOrders(): HasMany
    {
        return $this->hasMany(TenantOrder::class, 'commission_id');
    }
}