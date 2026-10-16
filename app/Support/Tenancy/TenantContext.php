<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;

class TenantContext
{
    protected ?Tenant $tenant = null;

    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function clear(): void
    {
        $this->tenant = null;
    }

    public function has(): bool
    {
        return $this->tenant !== null;
    }

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }
}
