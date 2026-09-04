<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * FR-TEN-01/UC-19 — Guest wajib diarahkan ke login saat akses route admin.
     */
    public function test_guest_diarahkan_ke_login_saat_akses_route_admin(): void
    {
        $response = $this->get('/admin/tenants');

        $response->assertRedirect('/login');
    }

    /**
     * FR-TEN-01/UC-19 — Guest wajib diarahkan ke login saat akses route tenant.
     */
    public function test_guest_diarahkan_ke_login_saat_akses_route_tenant(): void
    {
        $response = $this->get('/tenant/kantin-pusat/menu');

        $response->assertRedirect('/login');
    }

    /**
     * FR-TEN-01/UC-19 — User tanpa role tidak boleh akses route admin (403).
     */
    public function test_user_tanpa_role_ditolak_akses_admin(): void
    {
        $user = User::factory()->create([
            'role' => null,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/admin/tenants');

        $response->assertForbidden();
    }

    /**
     * FR-TEN-01/UC-19 — User dengan role admin berhasil akses dashboard admin.
     */
    public function test_user_dengan_role_admin_bisa_akses_dashboard_admin(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/admin/tenants');

        $response->assertOk();
    }
}
