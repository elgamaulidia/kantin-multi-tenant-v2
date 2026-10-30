<?php

namespace App\Modules\Catalog\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

final class TenantOpeningHours
{
    private function base(int $canteenId)
    {
        return DB::table('tenant_operating_hours as h')
            ->join('tenants as t', 't.id', '=', 'h.tenant_id')
            ->where('t.canteen_id', $canteenId)
            ->whereNull('t.deleted_at');
    }

    private function days(): array
    {
        $now = now('Asia/Jakarta');

        return [$now->dayOfWeekIso, $now->dayOfWeek];
    }

    public function hasHours(int $canteenId): bool
    {
        return $this->base($canteenId)->exists();
    }

    public function openTenantIds(int $canteenId): array
    {
        $time = now('Asia/Jakarta')->format('H:i:s');

        return $this->base($canteenId)
            ->whereIn('h.day_of_week', $this->days())
            ->where('h.opens_at', '<=', $time)
            ->where('h.closes_at', '>', $time)
            ->pluck('h.tenant_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function canteenIsOpen(int $canteenId): bool
    {
        return ! $this->hasHours($canteenId) || $this->openTenantIds($canteenId) !== [];
    }

    public function todayRange(int $canteenId): ?array
    {
        $row = $this->base($canteenId)
            ->whereIn('h.day_of_week', $this->days())
            ->selectRaw('MIN(h.opens_at) as opens, MAX(h.closes_at) as closes')
            ->first();

        if ($row === null || $row->opens === null) {
            return null;
        }

        return ['opens' => substr($row->opens, 0, 5), 'closes' => substr($row->closes, 0, 5)];
    }

    /** UC-01 alur 4b: tenant tanpa jadwal dianggap buka (konsisten dengan canteenIsOpen). */
    public function isOpen(Tenant $tenant): bool
    {
        $rows = DB::table('tenant_operating_hours')->where('tenant_id', $tenant->id);

        if (! (clone $rows)->exists()) {
            return true;
        }

        $time = now('Asia/Jakarta')->format('H:i:s');

        return (clone $rows)
            ->whereIn('day_of_week', $this->days())
            ->where('opens_at', '<=', $time)
            ->where('closes_at', '>', $time)
            ->exists();
    }

    /** Jam buka hari ini untuk tenant, format "10.00"; null bila tidak ada jadwal. */
    public function opensAtToday(Tenant $tenant): ?string
    {
        $opens = DB::table('tenant_operating_hours')
            ->where('tenant_id', $tenant->id)
            ->whereIn('day_of_week', $this->days())
            ->min('opens_at');

        return $opens === null ? null : str_replace(':', '.', substr($opens, 0, 5));
    }
}
