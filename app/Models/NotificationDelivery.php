<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_order_id',
        'idempotency_key',
        'status',
        'attempts',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'payload' => 'array',
        ];
    }

    public function tenantOrder(): BelongsTo
    {
        return $this->belongsTo(TenantOrder::class);
    }
}