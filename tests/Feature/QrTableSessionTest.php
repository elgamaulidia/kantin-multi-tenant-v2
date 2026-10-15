<?php

namespace Tests\Feature;

use App\Models\Canteen;
use App\Models\CustomerSession;
use App\Models\DiningTable;
use App\Models\TableQrToken;
use App\Modules\Ordering\Services\TableQrTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrTableSessionTest extends TestCase
{
    use RefreshDatabase;

    private function table(?Canteen $canteen = null, string $status = 'active'): DiningTable
    {
        $canteen ??= Canteen::factory()->create();

        return DiningTable::factory()->create(['canteen_id' => $canteen->id, 'status' => $status]);
    }

    private function issue(DiningTable $table): string
    {
        return app(TableQrTokenService::class)->issue($table)['token'];
    }

    public function test_rotate_revokes_old_token_and_keeps_single_active(): void
    {
        $table = $this->table();

        $plain1 = $this->issue($table);
        $this->get(route('customer.scan', ['token_meja' => $plain1]))->assertRedirect();

        $plain2 = $this->issue($table);

        $this->get(route('customer.scan', ['token_meja' => $plain1]))->assertNotFound();
        $this->get(route('customer.scan', ['token_meja' => $plain2]))->assertRedirect();

        $this->assertSame(1, TableQrToken::where('dining_table_id', $table->id)->where('status', 'active')->count());
    }

    public function test_valid_scan_creates_bound_session_with_secure_cookie(): void
    {
        $canteen = Canteen::factory()->create();
        $table = $this->table($canteen);
        $plain = $this->issue($table);

        $response = $this->get(route('customer.scan', ['token_meja' => $plain]));

        $response->assertRedirect('/'.$canteen->slug);

        $session = CustomerSession::firstOrFail();
        $this->assertSame($canteen->id, $session->canteen_id);
        $this->assertSame($table->id, $session->dining_table_id);

        $response->assertCookie('customer_session');
        $cookie = $response->getCookie('customer_session', false);
        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
    }

    public function test_invalid_tokens_are_generic_404(): void
    {
        $this->get(route('customer.scan', ['token_meja' => 'tidak-ada-token']))->assertNotFound();
        $this->get(route('customer.scan', ['token_meja' => '1']))->assertNotFound();

        $inactiveCanteen = Canteen::factory()->create(['status' => 'inactive']);
        $t2 = $this->table($inactiveCanteen);
        $this->get(route('customer.scan', ['token_meja' => $this->issue($t2)]))->assertNotFound();

        $t3 = $this->table();
        $plain3 = $this->issue($t3);
        $t3->forceFill(['status' => 'inactive'])->save();
        $this->get(route('customer.scan', ['token_meja' => $plain3]))->assertNotFound();
    }
}
