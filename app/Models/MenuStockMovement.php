<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuStockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'order_item_id',
        'idempotency_key',
        'type',
        'quantity_delta',
    ];

    protected function casts(): array
    {
        return [
            'quantity_delta' => 'integer',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
