<?php

namespace App\Modules\Admin\Services;

use App\Models\CommissionScheme;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class ScheduleCommission
{
    public function handle(
        Tenant $tenant,
        float $commissionRate,
        \DateTimeInterface $effectiveFrom
    ): CommissionScheme {
        if ($commissionRate < 0 || $commissionRate > 100) {
            throw new InvalidArgumentException(
                'Commission rate harus berada antara 0 dan 100.'
            );
        }

        return DB::transaction(function () use (
            $tenant,
            $commissionRate,
            $effectiveFrom
        ): CommissionScheme {
            $newEffectiveFrom = $effectiveFrom;

            $current = CommissionScheme::query()
                ->where('tenant_id', $tenant->id)
                ->whereNull('valid_to')
                ->lockForUpdate()
                ->first();

            if ($current !== null) {
                if ($newEffectiveFrom <= $current->valid_from) {
                    throw new InvalidArgumentException(
                        'Tanggal efektif baru harus setelah tanggal mulai komisi saat ini.'
                    );
                }

                $current->update([
                    'valid_to' => $newEffectiveFrom,
                ]);
            }

            return CommissionScheme::create([
                'tenant_id' => $tenant->id,
                'commission_rate' => $commissionRate,
                'valid_from' => $newEffectiveFrom,
                'valid_to' => null,
            ]);
        });
    }
}
