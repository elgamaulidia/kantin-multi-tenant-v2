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
        $routeTenant = $request->route('tenant');

        // Route yang memakai type-hint Tenant sudah di-bind menjadi model sebelum middleware ini jalan;
        // route lain masih membawa slug mentah.
        $tenant = $routeTenant instanceof Tenant
            ? $routeTenant
            : Tenant::where('slug', $routeTenant)->first();

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
