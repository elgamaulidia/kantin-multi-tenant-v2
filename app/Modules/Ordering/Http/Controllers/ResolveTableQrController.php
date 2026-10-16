<?php

namespace App\Modules\Ordering\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Services\TenantOpeningHours;
use App\Modules\Ordering\Services\ResolveTableScan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Endpoint publik pemindaian QR: /q/{token_meja}. Membuat sesi anonim, memasang cookie
 * aman, lalu mengarahkan ke katalog kantin hasil token (bukan input klien).
 * Token tidak valid -> 404 generik. Kantin di luar jam operasional -> halaman tutup,
 * sesi tidak dibuat.
 */
final class ResolveTableQrController extends Controller
{
    public function __construct(
        private ResolveTableScan $resolver,
        private TenantOpeningHours $hours,
    ) {}

    public function __invoke(Request $request, string $token_meja): RedirectResponse|View
    {
        $result = $this->resolver->resolve($token_meja);
        abort_if($result === null, 404);

        if (($result['closed'] ?? false) === true) {
            return view('canteen-closed', [
                'canteen' => $result['canteen'],
                'range' => $this->hours->todayRange($result['canteen']->id),
            ]);
        }

        $cookie = cookie(
            name: 'customer_session',
            value: $result['plain'],
            minutes: 240,
            path: '/',
            domain: null,
            secure: $request->isSecure() || app()->isProduction(),
            httpOnly: true,
            raw: false,
            sameSite: 'lax',
        );

        return redirect('/'.$result['canteen']->slug.'/meja')->withCookie($cookie);
    }
}
