<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modifier_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('group_id');
            $table->string('name', 80);
            $table->bigInteger('price_delta')->default(0);
            $table->unsignedInteger('stock_qty')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps(6);

            // FK komposit: modifier option harus merujuk group milik tenant yang sama
            // -> checkpoint utama Tahap 3: relasi lintas tenant gagal di database
            $table->foreign(['tenant_id', 'group_id'])
                ->references(['tenant_id', 'id'])
                ->on('modifier_groups')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modifier_options');
    }
};
