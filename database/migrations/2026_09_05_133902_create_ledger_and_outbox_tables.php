<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ledger_entries - append-only, tidak ada updated_at
        Schema::create('ledger_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('withdrawal_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('idempotency_key', 100);
            $table->enum('type', ['credit', 'debit', 'hold', 'release']);
            $table->bigInteger('available_delta')->default(0);
            $table->bigInteger('held_delta')->default(0);
            $table->timestamp('created_at', 6)->useCurrent();
            $table->unique(['idempotency_key']);
        });

        // outbox_events - append-only
        Schema::create('outbox_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('event_key', 100);
            $table->string('aggregate_type', 100);
            $table->string('aggregate_id', 100);
            $table->string('event_type', 100);
            $table->json('payload');
            $table->timestamp('published_at', 6)->nullable();
            $table->timestamp('created_at', 6)->useCurrent();
            $table->unique(['event_key']);
        });

        // notification_deliveries
        Schema::create('notification_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_order_id')->constrained()->restrictOnDelete();
            $table->string('idempotency_key', 100);
            $table->enum('status', ['pending', 'sent', 'failed', 'delivered'])->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->json('payload');
            $table->timestamps(6);
            $table->unique(['idempotency_key']);
        });

        // audit_logs - append-only
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('canteen_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('entity', 100);
            $table->string('action', 100);
            $table->string('request_id', 100)->nullable();
            $table->json('before_state')->nullable();
            $table->json('after_state')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at', 6)->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('outbox_events');
        Schema::dropIfExists('ledger_entries');
    }
};
