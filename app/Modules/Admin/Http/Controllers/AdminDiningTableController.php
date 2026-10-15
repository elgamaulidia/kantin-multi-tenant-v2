<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use App\Models\TableQrToken;
use App\Models\UserCanteenRole;
use App\Modules\Ordering\Services\TableQrTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RuntimeException;
use Illuminate\View\View;

class AdminDiningTableController extends Controller
{
    private function canteenId(Request $request): int
    {
        $canteenId = UserCanteenRole::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('role', ['owner', 'manager'])
            ->value('canteen_id');

        abort_unless($canteenId !== null, 403);

        return (int) $canteenId;
    }

    public function index(Request $request): View
    {
        $canteenId = $this->canteenId($request);

        $tables = DiningTable::query()
            ->where('canteen_id', $canteenId)
            ->with(['qrTokens' => fn ($query) => $query->latest('issued_at')])
            ->orderBy('code')
            ->get();

        return view('admin::tables.index', compact('tables'));
    }

    public function store(Request $request): RedirectResponse
    {
        $canteenId = $this->canteenId($request);

        $validated = $request->validate([
            'code' => [
                'required', 'alpha_dash', 'max:30',
                Rule::unique('dining_tables', 'code')
                    ->where('canteen_id', $canteenId),
            ],
            'label' => ['required', 'string', 'max:60'],
            'zone' => ['nullable', 'string', 'max:60'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        DiningTable::query()->create([
            ...$validated,
            'canteen_id' => $canteenId,
        ]);

        return redirect()->route('admin.tables.index')
            ->with('status', 'Meja berhasil ditambahkan.');
    }

    public function update(Request $request, int $table): RedirectResponse
    {
        $canteenId = $this->canteenId($request);

        $diningTable = DiningTable::query()
            ->where('canteen_id', $canteenId)
            ->findOrFail($table);

        $validated = $request->validate([
            'code' => [
                'required', 'alpha_dash', 'max:30',
                Rule::unique('dining_tables', 'code')
                    ->where('canteen_id', $canteenId)
                    ->ignore($diningTable->id),
            ],
            'label' => ['required', 'string', 'max:60'],
            'zone' => ['nullable', 'string', 'max:60'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $diningTable->update($validated);

        if ($diningTable->status === 'inactive') {
            DB::transaction(function () use ($diningTable) {
                TableQrToken::query()
                    ->where('dining_table_id', $diningTable->id)
                    ->where('status', 'active')
                    ->update([
                        'status' => 'revoked',
                        'revoked_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
            });
        }

        return redirect()->route('admin.tables.index')
            ->with('status', 'Data meja berhasil diperbarui.');
    }

    public function issueQr(
        Request $request,
        int $table,
        TableQrTokenService $service
    ): RedirectResponse {
        $canteenId = $this->canteenId($request);

        $diningTable = DiningTable::query()
            ->where('canteen_id', $canteenId)
            ->findOrFail($table);

        try {
            $issued = $service->issue($diningTable);
        } catch (RuntimeException $exception) {
            return redirect()->route('admin.tables.index')
                ->withErrors(['qr' => $exception->getMessage()]);
        }

        $scanUrl = route('customer.scan', [
            'token_meja' => $issued['token'],
        ]);

        return redirect()->route('admin.tables.index')
            ->with('status', 'QR baru berhasil diterbitkan.')
            ->with('issued_qr_url', $scanUrl);
    }

    public function revokeQr(
        Request $request,
        int $table,
        int $qr
    ): RedirectResponse {
        $canteenId = $this->canteenId($request);

        $diningTable = DiningTable::query()
            ->where('canteen_id', $canteenId)
            ->findOrFail($table);

        $token = TableQrToken::query()
            ->where('dining_table_id', $diningTable->id)
            ->whereKey($qr)
            ->where('status', 'active')
            ->firstOrFail();

        $token->update([
            'status' => 'revoked',
            'revoked_at' => Carbon::now(),
        ]);

        return redirect()->route('admin.tables.index')
            ->with('status', 'QR berhasil dicabut.');
    }
}
