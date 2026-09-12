<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('category_id');
            $table->string('name', 120);
            $table->unsignedBigInteger('base_price');
            $table->unsignedInteger('stock_qty')->default(0);
            $table->boolean('is_available')->default(true);
            $table->unsignedSmallInteger('prep_minutes')->default(10);
            $table->timestamps(6);

            // Sasaran FK komposit dari order_items nanti (tenant_id, menu_id)
            $table->unique(['tenant_id', 'id']);

            // FK komposit: category harus milik tenant yang sama
            $table->foreign(['tenant_id', 'category_id'])
                ->references(['tenant_id', 'id'])
                ->on('menu_categories')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};