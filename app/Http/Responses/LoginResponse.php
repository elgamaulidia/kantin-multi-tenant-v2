<?php

namespace App\Http\Responses;

use App\Http\Responses\Concerns\RedirectsToCurrentTeam;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    use RedirectsToCurrentTeam;

    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['two_factor' => false], 200);
        }

        // FR-TEN-01 / UC-19 — redirect pascalogin sesuai role.
        // Role "tenant" belum diarahkan ke dashboard tenant karena tabel
        // `tenants` belum dibuat pada Modul 2; akan dilengkapi saat modul
        // manajemen tenant tersedia.
        $user = $request->user();

        if ($user && $user->role === 'admin') {
            return redirect()->intended(route('admin.tenants.index'));
        }

        return redirect()->intended(
            $this->redirectPathForCurrentTeam($request, Fortify::redirects('login'))
        );
    }
}
