<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_orders', function (Blueprint $table): void {
            $table->index('tenant_id', 'tenant_orders_tenant_id_index');
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