<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 40);
            $table->string('order_number', 40);
            $table->foreignId('canteen_id')->constrained()->restrictOnDelete();
            $table->foreignUlid('customer_session_id')->constrained('customer_sessions')->restrictOnDelete();
            $table->string('checkout_key', 100);
            $table->addColumn('binary', 'tracking_token_hash', ['length' => 32]);
            $table->enum('status', ['draft', 'submitted', 'confirmed', 'completed', 'cancelled'])->default('draft');
            $table->unsignedBigInteger('total_amount');
            $table->json('customer_snapshot')->nullable();
            $table->json('table_snapshot')->nullable();
            $table->timestamps(6);
            $table->unique(['public_id']);
            $table->unique(['order_number']);
            $table->unique(['checkout_key']);
            $table->unique(['tracking_token_hash']);
        });

        Schema::create('tenant_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->foreignId('commission_id')->constrained('commission_schemes')->restrictOnDelete();
            $table->enum('status', ['pending', 'accepted', 'preparing', 'ready', 'served', 'cancelled'])->default('pending');
            $table->timestamp('scheduled_at', 6)->nullable();
            $table->unsignedBigInteger('subtotal_amount');
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('fee_amount')->default(0);
            $table->unsignedBigInteger('commission_amount')->default(0);
            $table->unsignedBigInteger('net_amount');
            $table->timestamps(6);
            $table->unique(['id', 'tenant_id']);
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_order_id')->constrained()->restrictOnDelete();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('menu_id');
            $table->string('name_snapshot', 120);
            $table->unsignedBigInteger('price_snapshot');
            $table->unsignedInteger('prep_minutes_snapshot')->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('modifier_total')->default(0);
            $table->unsignedBigInteger('line_total');
            $table->timestamps(6);
            $table->foreign(['tenant_id', 'menu_id'])->references(['tenant_id', 'id'])->on('menus')->restrictOnDelete();
        });

        Schema::create('order_item_modifiers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('modifier_group_id')->constrained()->restrictOnDelete();
            $table->foreignId('modifier_option_id')->constrained()->restrictOnDelete();
            $table->string('group_snapshot', 120);
            $table->string('option_snapshot', 120);
            $table->bigInteger('price_delta_snapshot');
            $table->timestamps(6);
        });

        Schema::create('menu_stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('idempotency_key', 100);
            $table->enum('type', ['reserve', 'release', 'deduct', 'restock']);
            $table->integer('quantity_delta');
            $table->timestamps(6);
            $table->unique(['idempotency_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_stock_movements');
        Schema::dropIfExists('order_item_modifiers');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('tenant_orders');
        Schema::dropIfExists('orders');
    }
};
