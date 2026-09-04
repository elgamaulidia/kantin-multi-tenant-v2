<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * FR-TEN-01 / UC-19 — hanya user dengan role yang sesuai boleh mengakses
     * route internal (tenant/admin). Menyembunyikan menu bukan pengganti ini;
     * server tetap wajib menolak dengan 403.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_if(! $user || ! in_array($user->role, $roles, true), 403);

        return $next($request);
    }
}
