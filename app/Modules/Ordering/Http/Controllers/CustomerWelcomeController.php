<?php

namespace App\Modules\Ordering\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Canteen;
use App\Models\CustomerSession;
use App\Models\DiningTable;
use App\Support\Tokens\OpaqueToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class CustomerWelcomeController extends Controller
{
    public function __invoke(Request $request, Canteen $canteen): View
    {
        $token = $request->cookie('customer_session');
        abort_if(! is_string($token) || $token === '', 404);

        $session = CustomerSession::query()
            ->where('session_token_hash', OpaqueToken::hash($token))
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();
        abort_if($session === null || (int) $session->canteen_id !== (int) $canteen->id, 404);

        $table = DiningTable::query()->findOrFail($session->dining_table_id);
        $tenants = DB::table('tenants')
            ->where('canteen_id', $canteen->id)
            ->whereNull('deleted_at')
            ->orderBy('display_name')
            ->get(['display_name', 'status']);

        return view('customer-welcome', compact('canteen', 'table', 'tenants'));
    }
}