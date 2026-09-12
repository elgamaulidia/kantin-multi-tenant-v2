<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiningTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'canteen_id',
        'code',
        'label',
        'zone',
        'status',
    ];

    public function canteen(): BelongsTo
    {
        return $this->belongsTo(Canteen::class);
    }

    public function qrTokens(): HasMany
    {
        return $this->hasMany(TableQrToken::class);
    }

    public function customerSessions(): HasMany
    {
        return $this->hasMany(CustomerSession::class);
    }
}