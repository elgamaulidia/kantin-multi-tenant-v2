<?php

namespace App\Http\Controllers\Modules\Ordering\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CustomerSession;
use App\Models\TableQrToken;
use App\Support\Tokens\OpaqueToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerQrScanController extends Controller
{
    public function __invoke(
        Request $request,
        string $token_meja
    ): RedirectResponse {
        $qr = TableQrToken::query()
            ->where('token_hash', OpaqueToken::hash($token_meja))
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with('diningTable.canteen')
            ->first();

        abort_if($qr === null, 404);

        $table = $qr->diningTable;

        abort_if(
            $table === null || $table->status !== 'active',
            404
        );

        $canteen = $table->canteen;

        abort_if(
            $canteen === null || $canteen->status !== 'active',
            404
        );

        $sessionToken = OpaqueToken::issue(32);

        CustomerSession::query()->create([
            'canteen_id' => $canteen->id,
            'dining_table_id' => $table->id,
            'table_qr_token_id' => $qr->id,
            'session_token_hash' => $sessionToken['hash'],
            'status' => 'active',
            'expires_at' => now()->addHours(4),
        ]);

        $cookie = cookie(
            name: 'customer_session',
            value: $sessionToken['plain'],
            minutes: 240,
            path: '/',
            domain: null,
            secure: $request->isSecure() || app()->isProduction(),
            httpOnly: true,
            raw: false,
            sameSite: 'lax',
        );

        return redirect()
            ->route('customer.home', [
                'canteen' => $canteen->slug,
            ])
            ->withCookie($cookie);
    }
}
