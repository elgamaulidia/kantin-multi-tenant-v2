<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_balances', function (Blueprint $table): void {
            $table->foreignId('tenant_id')->primary()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('available_amount')->default(0);
            $table->unsignedBigInteger('held_amount')->default(0);
            $table->timestamps(6);
        });

        // CHECK constraint sesuai ERD: saldo >= 0
        DB::statement('ALTER TABLE tenant_balances ADD CONSTRAINT chk_tenant_balances_nonneg CHECK (available_amount >= 0 AND held_amount >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_balances');
    }
};
