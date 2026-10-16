<?php

namespace Tests\Feature;

use App\Models\Canteen;
use App\Models\CustomerSession;
use App\Models\DiningTable;
use App\Modules\Ordering\Services\TableQrTokenService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class QrOperatingHoursTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function scene(?array $window): array
    {
        Carbon::setTestNow(Carbon::parse('2026-10-14 10:00:00', 'Asia/Jakarta'));

        $canteen = new Canteen;
        $canteen->forceFill([
            'code' => strtoupper(Str::random(5)),
            'slug' => 'kantin-'.Str::lower(Str::random(6)),
            'name' => 'Kantin Uji', 'tax_rate' => 0, 'service_fee_rate' => 0, 'status' => 'active',
        ])->save();

        $table = new DiningTable;
        $table->forceFill([
            'canteen_id' => $canteen->id, 'code' => 'T1', 'label' => 'Meja 1',
            'zone' => 'Indoor', 'status' => 'active',
        ])->save();

        $tenantId = DB::table('tenants')->insertGetId([
            'canteen_id' => $canteen->id, 'code' => 'AYAM', 'slug' => 'ayam',
            'display_name' => 'Ayam', 'status' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        if ($window !== null) {
            DB::table('tenant_operating_hours')->insert([
                'tenant_id' => $tenantId, 'day_of_week' => 3,
                'opens_at' => $window[0], 'closes_at' => $window[1],
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $issued = app(TableQrTokenService::class)->issue($table);

        return [$issued['token'], $canteen];
    }

    public function test_scan_creates_session_when_inside_opening_hours(): void
    {
        [$token] = $this->scene(['08:00:00', '17:00:00']);

        $response = $this->get('/q/'.$token);

        $this->assertNotSame(404, $response->status());
        $this->assertSame(1, CustomerSession::query()->count());
    }

    public function test_scan_outside_opening_hours_does_not_create_session(): void
    {
        [$token] = $this->scene(['13:00:00', '17:00:00']);

        $response = $this->get('/q/'.$token);

        $this->assertNotSame(404, $response->status());
        $this->assertSame(0, CustomerSession::query()->count());
    }

    public function test_closing_time_is_exclusive(): void
    {
        [$token] = $this->scene(['08:00:00', '10:00:00']);

        $this->get('/q/'.$token);

        $this->assertSame(0, CustomerSession::query()->count());
    }

    public function test_canteen_without_hours_is_treated_as_open(): void
    {
        [$token] = $this->scene(null);

        $this->get('/q/'.$token);

        $this->assertSame(1, CustomerSession::query()->count());
    }
}
