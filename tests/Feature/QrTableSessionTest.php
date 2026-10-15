<?php

namespace Tests\Feature;

use App\Models\Canteen;
use App\Models\CustomerSession;
use App\Models\DiningTable;
use App\Models\TableQrToken;
use App\Modules\Ordering\Services\TableQrTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class QrTableSessionTest extends TestCase
{
    use RefreshDatabase;

    private function canteen(string $status = 'active'): Canteen
    {
        $c = new Canteen;
        $c->forceFill([
            'code' => strtoupper(Str::random(5)),
            'slug' => 'kantin-'.Str::lower(Str::random(6)),
            'name' => 'Kantin Uji',
            'tax_rate' => 0,
            'service_fee_rate' => 0,
            'status' => $status,
        ])->save();

        return $c;
    }

    private function table(?Canteen $canteen = null, string $status = 'active'): DiningTable
    {
        $canteen ??= $this->canteen();
        $t = new DiningTable;
        $t->forceFill([
            'canteen_id' => $canteen->id,
            'code' => 'T'.random_int(1, 9999),
            'label' => 'Meja Uji',
            'zone' => 'Indoor',
            'status' => $status,
        ])->save();

        return $t;
    }

    private function issue(DiningTable $table): array
    {
        return app(TableQrTokenService::class)->issue($table);
    }
    public function test_valid_scan_creates_bound_session_with_secure_cookie(): void
    {
        $canteen = $this->canteen();
        $table = $this->table($canteen);
        $issued = $this->issue($table);

        $response = $this->get(route('customer.scan', ['token_meja' => $issued['token']]));

        $response->assertRedirect('/'.$canteen->slug);

        $session = CustomerSession::query()->firstOrFail();
        $this->assertSame($canteen->id, $session->canteen_id);
        $this->assertSame($table->id, $session->dining_table_id);
        $this->assertSame('active', $session->status);

        $cookie = $response->getCookie('customer_session', false);
        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
    }

    public function test_rotation_revokes_old_token_and_keeps_single_active(): void
    {
        $table = $this->table();
        $first = $this->issue($table);
        $second = $this->issue($table);

        $this->get(route('customer.scan', ['token_meja' => $first['token']]))->assertNotFound();
        $this->get(route('customer.scan', ['token_meja' => $second['token']]))->assertRedirect();

        $this->assertSame(1, TableQrToken::query()
            ->where('dining_table_id', $table->id)->where('status', 'active')->count());
    }
    public function test_invalid_tokens_return_generic_404(): void
    {
        $this->get(route('customer.scan', ['token_meja' => 'tidak-ada-token']))->assertNotFound();
        $this->get(route('customer.scan', ['token_meja' => '1']))->assertNotFound();

        $inactiveCanteen = $this->table($this->canteen('inactive'));
        $a = $this->issue($inactiveCanteen);
        $this->get(route('customer.scan', ['token_meja' => $a['token']]))->assertNotFound();

        $inactiveTable = $this->table(null, 'active');
        $b = $this->issue($inactiveTable);
        $inactiveTable->forceFill(['status' => 'inactive'])->save();
        $this->get(route('customer.scan', ['token_meja' => $b['token']]))->assertNotFound();

        $expiredTable = $this->table();
        $c = $this->issue($expiredTable);
        $c['record']->forceFill(['expires_at' => now()->subMinute()])->save();
        $this->get(route('customer.scan', ['token_meja' => $c['token']]))->assertNotFound();

        $this->assertSame(0, CustomerSession::query()->count());
    }
    public function test_token_is_opaque_and_stored_only_as_hash(): void
    {
        $issued = $this->issue($this->table());
        $stored = TableQrToken::query()->firstOrFail()->token_hash;

        $this->assertGreaterThanOrEqual(43, strlen($issued['token']));
        $this->assertSame(32, strlen($stored));
        $this->assertNotSame($issued['token'], $stored);
    }
}