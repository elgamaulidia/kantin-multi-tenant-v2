<?php

namespace App\Modules\Ordering\Services;

use App\Models\Canteen;
use App\Models\CustomerSession;
use App\Models\DiningTable;
use App\Models\TableQrToken;
use App\Support\Tokens\OpaqueToken;
use Illuminate\Support\Str;

/**
 * Menyelesaikan pemindaian QR: lookup hash token, validasi status/expiry/meja/kantin,
 * lalu membuat CustomerSession anonim terikat kantin + meja. Kegagalan bersifat generik.
 */
final class ResolveTableScan
{
    /**
     * @return array{plain: string, canteen: Canteen, table: DiningTable}|null
     */
    public function resolve(string $plainToken): ?array
    {
        $qr = TableQrToken::query()
            ->where('token_hash', OpaqueToken::hash($plainToken))
            ->first();

        if ($qr === null
            || $qr->status !== 'active'
            || $qr->revoked_at !== null
            || ($qr->expires_at !== null && $qr->expires_at->isPast())) {
            return null;
        }

        $table = DiningTable::query()->find($qr->dining_table_id);
        if ($table === null || $table->status !== 'active') {
            return null;
        }

        $canteen = Canteen::query()->find($table->canteen_id);
        if ($canteen === null || ($canteen->status ?? 'active') !== 'active') {
            return null;
        }

        $sessionToken = OpaqueToken::issue(32);

        (new CustomerSession)->forceFill([
            'id' => (string) Str::ulid(),
            'canteen_id' => $canteen->id,
            'dining_table_id' => $table->id,
            'table_qr_token_id' => $qr->id,
            'session_token_hash' => $sessionToken['hash'],
            'status' => 'active',
            'expires_at' => now()->addHours(4),
        ])->save();

        return ['plain' => $sessionToken['plain'], 'canteen' => $canteen, 'table' => $table];
    }
}