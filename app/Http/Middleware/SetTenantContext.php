<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\UserTenantRole;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    public function __construct(
        protected TenantContext $context,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenantSlug = $request->route('tenant');

        $tenant = Tenant::where('slug', $tenantSlug)->first();

        if (! $tenant || $tenant->status !== 'active') {
            abort(403, 'Tenant tidak aktif atau tidak ditemukan.');
        }

        $hasRole = UserTenantRole::where('user_id', $request->user()->id)
            ->where('tenant_id', $tenant->id)
            ->exists();

        if (! $hasRole) {
            abort(403, 'Anda tidak memiliki akses pada tenant ini.');
        }

        $this->context->set($tenant);

        try {
            return $next($request);
        } finally {
            $this->context->clear();
        }
    }
}
