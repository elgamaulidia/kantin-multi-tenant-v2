<?php

use App\Models\Canteen;
use App\Models\CustomerSession;
use App\Models\DiningTable;
use App\Models\TableQrToken;
use App\Support\Tokens\OpaqueToken;

function buatQrUntukPengujian(array $opsi = []): array
{
    $canteen = Canteen::factory()->create([
        'status' => $opsi['canteen_status'] ?? 'active',
    ]);

    $table = DiningTable::query()->create([
        'canteen_id' => $canteen->id,
        'code' => 'TEST-01',
        'label' => 'Meja Test 01',
        'status' => $opsi['table_status'] ?? 'active',
    ]);

    $issued = OpaqueToken::issue();

    $qr = TableQrToken::query()->create([
        'dining_table_id' => $table->id,
        'token_hash' => $issued['hash'],
        'status' => $opsi['qr_status'] ?? 'active',
        'issued_at' => now(),
        'expires_at' => $opsi['expires_at'] ?? null,
        'revoked_at' => $opsi['revoked_at'] ?? null,
    ]);

    return [$canteen, $table, $qr, $issued['plain']];
}

test('QR meja aktif membuat sesi pelanggan anonim', function () {
    [$canteen, $table, $qr, $plain] = buatQrUntukPengujian();

    $response = $this->get(route('customer.scan', [
        'token_meja' => $plain,
    ]));

    $response->assertRedirect(route('customer.welcome', [
        'canteen' => $canteen->slug,
    ]));

    $response->assertCookie('customer_session');

    $session = CustomerSession::query()->first();

    expect($session)->not->toBeNull()
        ->and($session->canteen_id)->toBe($canteen->id)
        ->and($session->dining_table_id)->toBe($table->id)
        ->and($session->table_qr_token_id)->toBe($qr->id)
        ->and($session->status)->toBe('active')
        ->and(strlen($session->session_token_hash))->toBe(32)
        ->and($session->expires_at->timestamp)
        ->toBeGreaterThanOrEqual(now()->addHours(4)->timestamp - 2)
        ->toBeLessThanOrEqual(now()->addHours(4)->timestamp + 2);
});

test('QR yang tidak dikenal ditolak', function () {
    $response = $this->get(route('customer.scan', [
        'token_meja' => 'token-tidak-valid',
    ]));

    $response->assertNotFound();

    expect(CustomerSession::query()->count())->toBe(0);
});

test('QR yang sudah kedaluwarsa ditolak', function () {
    [, , , $plain] = buatQrUntukPengujian([
        'expires_at' => now()->subMinute(),
    ]);

    $this->get(route('customer.scan', [
        'token_meja' => $plain,
    ]))->assertNotFound();

    expect(CustomerSession::query()->count())->toBe(0);
});

test('QR yang sudah dicabut ditolak', function () {
    [, , , $plain] = buatQrUntukPengujian([
        'qr_status' => 'revoked',
        'revoked_at' => now(),
    ]);

    $this->get(route('customer.scan', [
        'token_meja' => $plain,
    ]))->assertNotFound();

    expect(CustomerSession::query()->count())->toBe(0);
});

test('QR meja yang tidak aktif ditolak', function () {
    [, , , $plain] = buatQrUntukPengujian([
        'table_status' => 'inactive',
    ]);

    $this->get(route('customer.scan', [
        'token_meja' => $plain,
    ]))->assertNotFound();

    expect(CustomerSession::query()->count())->toBe(0);
});

test('QR dari kantin yang tidak aktif ditolak', function () {
    [, , , $plain] = buatQrUntukPengujian([
        'canteen_status' => 'inactive',
    ]);

    $this->get(route('customer.scan', [
        'token_meja' => $plain,
    ]))->assertNotFound();

    expect(CustomerSession::query()->count())->toBe(0);
});
