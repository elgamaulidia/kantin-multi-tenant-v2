<?php

namespace App\Modules\Catalog\Services;

use App\Models\AuditLog;
use App\Models\TenantOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AdminPlatformReportQuery
{
    /**
     * Ambil TenantOrder lintas tenant untuk laporan admin platform.
     *
     * Bypass global scope tenant SENGAJA dipakai di sini karena admin
     * platform perlu melihat data semua tenant untuk keperluan laporan.
     * Setiap pemanggilan WAJIB dicatat ke audit log (aktor + lingkup data).
     */
    public function allTenantOrders(User $actor, array $filters = []): Collection
    {
        $query = TenantOrder::withoutGlobalScopes();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['tenant_id'])) {
            $query->where('tenant_id', $filters['tenant_id']);
        }

        $results = $query->get();

        AuditLog::create([
            'actor_id' => $actor->id,
            'tenant_id' => $filters['tenant_id'] ?? null,
            'entity' => 'TenantOrder',
            'action' => 'admin.cross_tenant_report',
            'metadata' => [
                'filters' => $filters,
                'result_count' => $results->count(),
            ],
        ]);

        return $results;
    }
}
