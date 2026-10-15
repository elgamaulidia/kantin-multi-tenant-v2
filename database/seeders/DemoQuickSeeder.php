<?php

namespace Database\Seeders;

use App\Models\Canteen;
use App\Models\DiningTable;
use App\Models\User;
use App\Models\UserCanteenRole;
use App\Modules\Ordering\Services\TableQrTokenService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoQuickSeeder extends Seeder
{
    public function run(): void
    {
        $canteen = Canteen::query()->first();
        if ($canteen === null) {
            $canteen = new Canteen;
            $canteen->forceFill([
                'code' => 'KP', 'slug' => 'kantin-pusat', 'name' => 'Kantin Pusat',
                'tax_rate' => 0, 'service_fee_rate' => 0, 'status' => 'active',
            ])->save();
        }

        $user = User::query()->where('email', 'admin@kantin.test')->first() ?? new User;
        $user->forceFill([
            'name' => 'Admin Kantin',
            'email' => 'admin@kantin.test',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
            'role' => 'admin',
            'account_status' => 'active',
        ])->save();

        $hasRole = UserCanteenRole::query()
            ->where('user_id', $user->id)->where('canteen_id', $canteen->id)->exists();
        if (! $hasRole) {
            (new UserCanteenRole)->forceFill([
                'user_id' => $user->id, 'canteen_id' => $canteen->id, 'role' => 'manager',
            ])->save();
        }

        $table = DiningTable::query()->where('canteen_id', $canteen->id)->where('code', 'T1')->first();
        if ($table === null) {
            $table = new DiningTable;
            $table->forceFill([
                'canteen_id' => $canteen->id, 'code' => 'T1', 'label' => 'Meja 1',
                'zone' => 'Indoor', 'status' => 'active',
            ])->save();
        }

        $issued = app(TableQrTokenService::class)->issue($table);
        $this->command->info('Kantin : '.$canteen->slug);
        $this->command->info('URL scan: '.route('customer.scan', ['token_meja' => $issued['token']]));
    }
}