<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'canteen_id',
        'code',
        'slug',
        'display_name',
        'status',
    ];

    public function canteen(): BelongsTo
    {
        return $this->belongsTo(Canteen::class);
    }

    public function commissionSchemes(): HasMany
    {
        return $this->hasMany(CommissionScheme::class);
    }
}
