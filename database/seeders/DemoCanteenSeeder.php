<?php

namespace Database\Seeders;

use App\Models\Canteen;
use App\Models\CommissionScheme;
use App\Models\DiningTable;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\TableQrToken;
use App\Models\Tenant;
use App\Models\TenantBalance;
use App\Models\User;
use App\Models\UserCanteenRole;
use App\Models\UserTenantRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoCanteenSeeder extends Seeder
{
    /**
     * Seeder idempoten: dijalankan ulang setelah migrate:fresh tanpa
     * menggandakan data, memakai updateOrCreate berbasis natural key
     * (code/slug/email), bukan create() polos.
     */
    public function run(): void
    {
        // 1. Satu kantin
        $canteen = Canteen::updateOrCreate(
            ['code' => 'KTN01'],
            [
                'slug' => 'kantin-demo',
                'name' => 'Kantin Demo Kampus',
                'tax_rate' => 10.00,
                'service_fee_rate' => 2.00,
                'status' => 'active',
            ]
        );

        // 2. User admin kantin + role
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@kantin-demo.test'],
            [
                'name' => 'Admin Kantin Demo',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'account_status' => 'active',
            ]
        );

        UserCanteenRole::updateOrCreate(
            ['user_id' => $adminUser->id, 'canteen_id' => $canteen->id],
            ['role' => 'owner']
        );

        // 3. Dua tenant di bawah kantin ini
        $tenantsData = [
            [
                'code' => 'T01',
                'slug' => 'nasi-goreng-bu-siti',
                'display_name' => 'Nasi Goreng Bu Siti',
                'owner_email' => 'siti@kantin-demo.test',
                'owner_name' => 'Bu Siti',
                'commission_rate' => 5.00,
            ],
            [
                'code' => 'T02',
                'slug' => 'es-teh-pak-budi',
                'display_name' => 'Es Teh Pak Budi',
                'owner_email' => 'budi@kantin-demo.test',
                'owner_name' => 'Pak Budi',
                'commission_rate' => 7.50,
            ],
        ];

        foreach ($tenantsData as $data) {
            $tenant = Tenant::updateOrCreate(
                ['canteen_id' => $canteen->id, 'code' => $data['code']],
                [
                    'slug' => $data['slug'],
                    'display_name' => $data['display_name'],
                    'status' => 'active',
                ]
            );

            // Saldo tenant (tenant_balances, primary key = tenant_id)
            TenantBalance::updateOrCreate(
                ['tenant_id' => $tenant->id],
                ['available_amount' => 0, 'held_amount' => 0]
            );

            // Role: owner tenant
            $ownerUser = User::updateOrCreate(
                ['email' => $data['owner_email']],
                [
                    'name' => $data['owner_name'],
                    'password' => Hash::make('password'),
                    'role' => 'tenant',
                    'tenant_id' => $tenant->id,
                    'account_status' => 'active',
                ]
            );

            UserTenantRole::updateOrCreate(
                ['user_id' => $ownerUser->id, 'tenant_id' => $tenant->id],
                ['role' => 'owner']
            );

            // Komisi berlaku sejak sekarang
            CommissionScheme::updateOrCreate(
                ['tenant_id' => $tenant->id, 'valid_from' => now()->startOfDay()],
                [
                    'commission_rate' => $data['commission_rate'],
                    'valid_to' => null,
                ]
            );

            // Satu kategori + dua menu per tenant
            $category = MenuCategory::updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'Menu Utama'],
                ['sort_order' => 1, 'is_active' => true]
            );

            $menu = Menu::updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $data['display_name'].' - Reguler'],
                [
                    'category_id' => $category->id,
                    'base_price' => 15000,
                    'stock_qty' => 50,
                    'is_available' => true,
                    'prep_minutes' => 10,
                ]
            );

            // Satu modifier group + dua opsi per tenant, ditempel ke menu
            $modifierGroup = ModifierGroup::updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'Tingkat Pedas'],
                ['min_select' => 0, 'max_select' => 1, 'is_active' => true]
            );

            ModifierOption::updateOrCreate(
                ['tenant_id' => $tenant->id, 'group_id' => $modifierGroup->id, 'name' => 'Pedas'],
                ['price_delta' => 0, 'stock_qty' => null, 'is_available' => true]
            );

            ModifierOption::updateOrCreate(
                ['tenant_id' => $tenant->id, 'group_id' => $modifierGroup->id, 'name' => 'Extra Pedas'],
                ['price_delta' => 2000, 'stock_qty' => null, 'is_available' => true]
            );

            $menu->modifierGroups()->syncWithoutDetaching([
                $modifierGroup->id => ['tenant_id' => $tenant->id, 'sort_order' => 1],
            ]);
        }

        // 4. Meja: tiga dining table + QR token untuk kantin ini
        for ($i = 1; $i <= 3; $i++) {
            $table = DiningTable::updateOrCreate(
                ['canteen_id' => $canteen->id, 'code' => 'MJ-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT)],
                [
                    'label' => 'Meja '.$i,
                    'zone' => 'Utama',
                    'status' => 'active',
                ]
            );

            // token_hash bertipe BINARY(32) dan wajib diisi (tidak nullable)
            TableQrToken::updateOrCreate(
                ['dining_table_id' => $table->id, 'status' => 'active'],
                [
                    'token_hash' => hash('sha256', 'table-token-'.$table->id, true),
                    'issued_at' => now(),
                    'expires_at' => null,
                    'revoked_at' => null,
                ]
            );
        }
    }
}
