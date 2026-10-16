<?php

namespace App\Modules\Ordering\Services;

use App\Models\DiningTable;
use App\Models\TableQrToken;
use App\Support\Tokens\OpaqueToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class TableQrTokenService
{
    /**
     * Menerbitkan token QR baru untuk meja.
     *
     * Token asli hanya dikembalikan saat diterbitkan.
     * Database hanya menyimpan hash token.
     *
     * @return array{record: TableQrToken, token: string}
     */
    public function issue(DiningTable $table): array
    {
        return DB::transaction(function () use ($table): array {
            $lockedTable = DiningTable::query()
                ->lockForUpdate()
                ->findOrFail($table->getKey());

            if ($lockedTable->status !== 'active') {
                throw new RuntimeException(
                    'QR tidak dapat diterbitkan untuk meja yang tidak aktif.'
                );
            }

            $now = Carbon::now();

            TableQrToken::query()
                ->where('dining_table_id', $lockedTable->id)
                ->where('status', 'active')
                ->update([
                    'status' => 'revoked',
                    'revoked_at' => $now,
                    'updated_at' => $now,
                ]);

            $issued = OpaqueToken::issue();

            $record = TableQrToken::query()->create([
                'dining_table_id' => $lockedTable->id,
                'token_hash' => $issued['hash'],
                'status' => 'active',
                'issued_at' => $now,
                'expires_at' => null,
                'revoked_at' => null,
            ]);

            return [
                'record' => $record,
                'token' => $issued['plain'],
            ];
        });
    }
}
