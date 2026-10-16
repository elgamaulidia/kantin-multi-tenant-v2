<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;
use App\Models\UserTenantRole;

class MenuPolicy
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

    public function view(User $user, Menu $menu): bool
    {
        return $this->hasTenantAccess($user, $menu->tenant_id);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Menu $menu): bool
    {
        return $this->hasTenantAccess($user, $menu->tenant_id);
    }

    public function delete(User $user, Menu $menu): bool
    {
        return $this->hasTenantAccess($user, $menu->tenant_id);
    }

    public function restore(User $user, Menu $menu): bool
    {
        return $this->hasTenantAccess($user, $menu->tenant_id);
    }

    public function forceDelete(User $user, Menu $menu): bool
    {
        return $this->hasTenantAccess($user, $menu->tenant_id);
    }
}
