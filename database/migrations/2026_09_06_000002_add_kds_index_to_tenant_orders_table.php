<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Temuan dari EXPLAIN Tahap 6: query Kitchen Display System
     * (filter tenant_id + status, sort scheduled_at) menghasilkan
     * "Using filesort" karena belum ada index yang mencakup
     * kombinasi kolom tersebut. Sesuai troubleshooting modul:
     * "Query laporan lambat -> Index filter belum ada -> Tambahkan
     * composite index -> EXPLAIN memilih index".
     */
    public function up(): void
    {
        Schema::table('tenant_orders', function (Blueprint $table): void {
            $table->index(['tenant_id', 'status', 'scheduled_at'], 'tenant_orders_kds_index');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_orders', function (Blueprint $table): void {
            $table->dropIndex('tenant_orders_kds_index');
        });
    }
};