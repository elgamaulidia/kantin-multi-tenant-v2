<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantBalance extends Model
{
    use HasFactory;

    protected $primaryKey = 'tenant_id';

    public $incrementing = false;

    protected $keyType = 'integer';

    protected $fillable = [
        'tenant_id',
        'available_amount',
        'held_amount',
    ];

    protected function casts(): array
    {
        return [
            'available_amount' => 'integer',
            'held_amount' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
