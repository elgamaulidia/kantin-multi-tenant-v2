<?php

namespace App\Policies;

use App\Models\TenantOrder;
use App\Models\User;
use App\Models\UserTenantRole;

class TenantOrderPolicy
{
    protected function hasTenantAccess(User $user, int $tenantId): bool
    {
        return UserTenantRole::where('user_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->exists();
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TenantOrder $tenantOrder): bool
    {
        return $this->hasTenantAccess($user, $tenantOrder->tenant_id);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TenantOrder $tenantOrder): bool
    {
        return $this->hasTenantAccess($user, $tenantOrder->tenant_id);
    }

    public function delete(User $user, TenantOrder $tenantOrder): bool
    {
        return $this->hasTenantAccess($user, $tenantOrder->tenant_id);
    }

    public function restore(User $user, TenantOrder $tenantOrder): bool
    {
        return $this->hasTenantAccess($user, $tenantOrder->tenant_id);
    }

    public function forceDelete(User $user, TenantOrder $tenantOrder): bool
    {
        return $this->hasTenantAccess($user, $tenantOrder->tenant_id);
    }
}
