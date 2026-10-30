<?php

namespace Tests\Feature;

use App\Models\Canteen;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Tenant;
use App\Modules\Kitchen\Services\WaitTimeEstimator;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * UC-01 Telusuri Menu & Tenant (kelompok per tenant, cari nama tenant/kategori, chip kategori,
 * tenant tutup) dan UC-11 Hitung Estimasi Waktu Tunggu (alur 1a: tanpa antrean).
 */
class CatalogBrowseUseCaseTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // BelongsToTenant menolak create() saat context kosong, jadi isi sementara lalu kosongkan.
    private function menu(Tenant $tenant, string $category, array $attrs = []): Menu
    {
        $context = app(TenantContext::class);
        $context->set($tenant);

        $cat = MenuCategory::query()->withoutGlobalScope('tenant')->where(['tenant_id' => $tenant->id, 'name' => $category])->first()
            ?? MenuCategory::factory()->create(['tenant_id' => $tenant->id, 'name' => $category]);

        $menu = Menu::factory()->create(array_merge([
            'tenant_id' => $tenant->id, 'category_id' => $cat->id,
            'is_available' => true, 'stock_qty' => 10, 'prep_minutes' => 8,
        ], $attrs));

        $context->clear();

        return $menu;
    }

    public function test_catalog_groups_by_tenant_and_filters_by_tenant_name_and_category(): void
    {
        $canteen = Canteen::factory()->create();
        $rina = Tenant::factory()->create(['canteen_id' => $canteen->id, 'display_name' => 'Warung Bu Rina']);
        $kopi = Tenant::factory()->create(['canteen_id' => $canteen->id, 'display_name' => 'Kopi Serambi']);
        $this->menu($rina, 'Makanan utama', ['name' => 'Nasi Ayam Bakar', 'description' => 'Ayam bakar kecap']);
        $this->menu($kopi, 'Minuman', ['name' => 'Es Kopi Susu']);

        Livewire::test('catalog::menu-catalog', ['canteenSlug' => $canteen->slug])
            ->assertSeeInOrder(['Warung Bu Rina', 'Nasi Ayam Bakar', 'Ayam bakar kecap', 'Kopi Serambi', 'Es Kopi Susu'])
            ->set('search', 'Serambi')->assertSee('Es Kopi Susu')->assertDontSee('Nasi Ayam Bakar')
            ->set('search', '')->call('filterCategory', 'Makanan utama')->assertSee('Nasi Ayam Bakar')->assertDontSee('Es Kopi Susu');
    }

    public function test_closed_tenant_shows_opening_time_and_items_are_not_sellable(): void
    {
        $canteen = Canteen::factory()->create();
        $geprek = Tenant::factory()->create(['canteen_id' => $canteen->id, 'display_name' => 'Geprek Juara']);
        foreach (range(0, 6) as $day) {
            DB::table('tenant_operating_hours')->insert([
                'tenant_id' => $geprek->id, 'day_of_week' => $day,
                'opens_at' => '10:00:00', 'closes_at' => '20:00:00',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->menu($geprek, 'Makanan utama', ['name' => 'Ayam Geprek']);
        $this->travelTo(Carbon::parse('2026-09-24 08:00', 'Asia/Jakarta'));

        Livewire::test('catalog::menu-catalog', ['canteenSlug' => $canteen->slug])
            ->assertSee('Tutup · Buka pukul 10.00')
            ->assertSeeInOrder(['data-sellable="0"', 'Ayam Geprek'], false);
    }

    public function test_wait_estimate_without_queue_equals_item_prep_time(): void
    {
        $canteen = Canteen::factory()->create();
        $tenant = Tenant::factory()->create(['canteen_id' => $canteen->id]);
        $this->menu($tenant, 'Makanan utama', ['prep_minutes' => 8]);
        $estimator = app(WaitTimeEstimator::class);

        // Alur 1a: tanpa antrean aktif, estimasi hanya waktu siap item (8 mnt, rentang 8–12).
        $this->assertSame(0.0, $estimator->queueMinutes($tenant));
        $this->assertSame(['low' => 8, 'high' => 12], $estimator->forTenant($tenant));
        $this->assertSame('± 8–12 mnt', $estimator->label($tenant));
    }
}
