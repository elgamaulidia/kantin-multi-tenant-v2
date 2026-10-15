<?php

namespace App\Modules\Ordering\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ordering\Services\ResolveTableScan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Endpoint publik pemindaian QR: /q/{token_meja}. Membuat sesi anonim, memasang cookie
 * aman, lalu mengarahkan ke katalog kantin hasil token (bukan input klien).
 * Token tidak valid -> 404 generik.
 */
final class ResolveTableQrController extends Controller
{
    public function __construct(private ResolveTableScan $resolver) {}

    public function __invoke(Request $request, string $token_meja): RedirectResponse
    {
        $result = $this->resolver->resolve($token_meja);
        abort_if($result === null, 404);

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
