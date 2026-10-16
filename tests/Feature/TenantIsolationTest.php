<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserTenantRole;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected User $userA;

    protected User $userB;

    protected Menu $menuA;

    protected Menu $menuB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::factory()->create();
        $this->tenantB = Tenant::factory()->create();

        $this->userA = User::factory()->create();
        $this->userB = User::factory()->create();

        UserTenantRole::factory()->create([
            'user_id' => $this->userA->id,
            'tenant_id' => $this->tenantA->id,
            'role' => 'staff',
        ]);

        UserTenantRole::factory()->create([
            'user_id' => $this->userB->id,
            'tenant_id' => $this->tenantB->id,
            'role' => 'staff',
        ]);

        // Isi TenantContext sementara untuk seeding data lewat factory,
        // karena trait BelongsToTenant menolak create() saat context kosong.
        $context = app(TenantContext::class);

        $context->set($this->tenantA);
        $this->menuA = Menu::factory()->create(['tenant_id' => $this->tenantA->id]);
        $context->clear();

        $context->set($this->tenantB);
        $this->menuB = Menu::factory()->create(['tenant_id' => $this->tenantB->id]);
        $context->clear();
    }

    public function test_query_biasa_hanya_mengembalikan_tenant_aktif(): void
    {
        $context = app(TenantContext::class);
        $context->set($this->tenantA);

        $menus = Menu::all();

        $this->assertTrue($menus->contains('id', $this->menuA->id));
        $this->assertFalse($menus->contains('id', $this->menuB->id));

        $context->clear();
    }

    public function test_tenant_a_tidak_bisa_update_menu_tenant_b(): void
    {
        $this->actingAs($this->userA)
            ->put("/tenant/{$this->tenantA->slug}/menu/{$this->menuB->id}", [
                'name' => 'Hasil Suntingan Ilegal',
            ])
            ->assertStatus(404);
    }

    public function test_tenant_a_tidak_bisa_delete_menu_tenant_b(): void
    {
        $this->actingAs($this->userA)
            ->delete("/tenant/{$this->tenantA->slug}/menu/{$this->menuB->id}")
            ->assertStatus(404);
    }

    public function test_tenant_suspended_mendapat_403(): void
    {
        $this->tenantA->update(['status' => 'suspended']);

        $this->actingAs($this->userA)
            ->get("/tenant/{$this->tenantA->slug}/menu")
            ->assertStatus(403);
    }

    public function test_user_tanpa_role_mendapat_403(): void
    {
        $this->actingAs($this->userB)
            ->get("/tenant/{$this->tenantA->slug}/menu")
            ->assertStatus(403);
    }

    public function test_data_tenant_b_tidak_berubah_setelah_percobaan_akses_ilegal(): void
    {
        $originalName = $this->menuB->name;

        $this->actingAs($this->userA)
            ->put("/tenant/{$this->tenantA->slug}/menu/{$this->menuB->id}", [
                'name' => 'Hasil Suntingan Ilegal',
            ]);

        $this->menuB->refresh();

        $this->assertEquals($originalName, $this->menuB->name);
    }
}
