<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantBankAccount extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'verified_by',
        'bank_name',
        'account_number_cipher',
        'account_number_last4',
        'status',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class, 'bank_account_id');
    }
}