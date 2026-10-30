<?php

namespace Tests\Feature;

use App\Models\Canteen;
use App\Models\DiningTable;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\TableQrToken;
use App\Models\Tenant;
use App\Modules\Kitchen\Services\WaitTimeEstimator;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * UC-11 Hitung Estimasi Waktu Tunggu, alur utama: antrean aktif tenant dibagi kapasitas paralel
 * dapur, ditambah waktu siap item. Hanya status accepted dan preparing yang dihitung, dan antrean
 * tenant lain tidak boleh bocor ke estimasi tenant ini.
 */
class WaitTimeEstimatorTest extends TestCase
{
    use RefreshDatabase;

    private int $orderSeq = 0;

    // BelongsToTenant menolak create() saat context kosong, jadi isi sementara lalu kosongkan.
    private function menu(Tenant $tenant, array $attrs = []): Menu
    {
        $context = app(TenantContext::class);
        $context->set($tenant);

        $category = MenuCategory::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Makanan utama']);
        $menu = Menu::factory()->create(array_merge([
            'tenant_id' => $tenant->id, 'category_id' => $category->id,
            'is_available' => true, 'stock_qty' => 10, 'prep_minutes' => 8,
        ], $attrs));

        $context->clear();

        return $menu;
    }

    /** Sesi pelanggan yang valid: kantin, meja, token QR, lalu sesi aktif. */
    private function customerSession(Canteen $canteen): string
    {
        $table = new DiningTable;
        $table->forceFill([
            'canteen_id' => $canteen->id, 'code' => 'T'.Str::random(4), 'label' => 'Meja Uji',
            'zone' => 'Indoor', 'status' => 'active',
        ])->save();

        $qr = new TableQrToken;
        $qr->forceFill([
            'dining_table_id' => $table->id, 'token_hash' => random_bytes(32), 'status' => 'active',
            'issued_at' => now(), 'expires_at' => null, 'revoked_at' => null,
        ])->save();

        $sessionId = (string) Str::ulid();
        DB::table('customer_sessions')->insert([
            'id' => $sessionId, 'canteen_id' => $canteen->id, 'dining_table_id' => $table->id,
            'table_qr_token_id' => $qr->id, 'session_token_hash' => random_bytes(32), 'status' => 'active',
            'expires_at' => now()->addHours(4), 'created_at' => now(), 'updated_at' => now(),
        ]);

        return $sessionId;
    }

    /** Satu pesanan dengan satu sub-pesanan tenant berstatus tertentu dan satu item. */
    private function subOrder(Canteen $canteen, string $sessionId, Tenant $tenant, Menu $menu, int $commissionId, string $status, int $prep): void
    {
        $orderId = DB::table('orders')->insertGetId([
            'public_id' => (string) Str::uuid(), 'order_number' => 'ORD-'.++$this->orderSeq,
            'canteen_id' => $canteen->id, 'customer_session_id' => $sessionId,
            'checkout_key' => (string) Str::uuid(), 'tracking_token_hash' => random_bytes(32),
            'status' => 'confirmed', 'total_amount' => 10000, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $tenantOrderId = DB::table('tenant_orders')->insertGetId([
            'order_id' => $orderId, 'tenant_id' => $tenant->id, 'commission_id' => $commissionId,
            'status' => $status, 'subtotal_amount' => 10000, 'net_amount' => 9000,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('order_items')->insert([
            'tenant_order_id' => $tenantOrderId, 'tenant_id' => $tenant->id, 'menu_id' => $menu->id,
            'name_snapshot' => 'Item uji', 'price_snapshot' => 10000, 'prep_minutes_snapshot' => $prep,
            'quantity' => 1, 'line_total' => 10000, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function commission(Tenant $tenant): int
    {
        return DB::table('commission_schemes')->insertGetId([
            'tenant_id' => $tenant->id, 'commission_rate' => 10.00,
            'valid_from' => now()->subDay(), 'valid_to' => null,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_active_queue_is_divided_by_kitchen_capacity_and_adds_item_prep(): void
    {
        $canteen = Canteen::factory()->create();
        $tenant = Tenant::factory()->create(['canteen_id' => $canteen->id]);
        $menu = $this->menu($tenant, ['prep_minutes' => 8]);
        $commission = $this->commission($tenant);
        $session = $this->customerSession($canteen);

        // Dua pesanan aktif @10 mnt, ditambah satu yang sudah ready (tidak dihitung).
        foreach (['accepted', 'preparing', 'ready'] as $status) {
            $this->subOrder($canteen, $session, $tenant, $menu, $commission, $status, 10);
        }

        $estimator = app(WaitTimeEstimator::class);

        // Antrean (10 + 10) / kapasitas paralel 2 = 10 mnt; ditambah item 8 mnt = 18, rentang 18–27.
        $this->assertSame(10.0, $estimator->queueMinutes($tenant), 'status ready tidak dihitung');
        $this->assertSame(['low' => 18, 'high' => 27], $estimator->forTenant($tenant));
        $this->assertSame('± 18–27 mnt', $estimator->label($tenant));

        Livewire::test('catalog::menu-catalog', ['canteenSlug' => $canteen->slug])
            ->assertSee('Antrean ± 18–27 mnt');
    }

    public function test_other_tenant_queue_does_not_leak_into_estimate(): void
    {
        $canteen = Canteen::factory()->create();
        $tenant = Tenant::factory()->create(['canteen_id' => $canteen->id]);
        $other = Tenant::factory()->create(['canteen_id' => $canteen->id]);
        $menu = $this->menu($tenant, ['prep_minutes' => 8]);
        $otherMenu = $this->menu($other, ['prep_minutes' => 8]);
        $session = $this->customerSession($canteen);

        // Antrean berat hanya di tenant lain; tenant ini kosong.
        $otherCommission = $this->commission($other);
        foreach (range(1, 3) as $i) {
            $this->subOrder($canteen, $session, $other, $otherMenu, $otherCommission, 'preparing', 30);
        }

        $estimator = app(WaitTimeEstimator::class);

        $this->assertSame(0.0, $estimator->queueMinutes($tenant));
        $this->assertSame(['low' => 8, 'high' => 12], $estimator->forTenant($tenant));
        $this->assertSame(45.0, $estimator->queueMinutes($other), '(30+30+30) / 2');
    }
}
