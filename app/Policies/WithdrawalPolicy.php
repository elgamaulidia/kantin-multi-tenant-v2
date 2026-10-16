<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserTenantRole;
use App\Models\Withdrawal;

class WithdrawalPolicy
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

    public function view(User $user, Withdrawal $withdrawal): bool
    {
        return $this->hasTenantAccess($user, $withdrawal->tenant_id);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Withdrawal $withdrawal): bool
    {
        return $this->hasTenantAccess($user, $withdrawal->tenant_id);
    }

    public function delete(User $user, Withdrawal $withdrawal): bool
    {
        return $this->hasTenantAccess($user, $withdrawal->tenant_id);
    }

    public function restore(User $user, Withdrawal $withdrawal): bool
    {
        return $this->hasTenantAccess($user, $withdrawal->tenant_id);
    }

    public function forceDelete(User $user, Withdrawal $withdrawal): bool
    {
        return $this->hasTenantAccess($user, $withdrawal->tenant_id);
    }
}
