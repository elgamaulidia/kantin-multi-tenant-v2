<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();

            // TODO: pastikan ini FK ke tabel yang benar sesuai garis ERD.
            // Sementara diasumsikan ke tenant_orders karena tenant_id/snapshot
            // komisi memang disimpan di sana (bukan di orders induk).
            // Ganti nama kolom & constrained() kalau ERD kamu menunjuk ke tabel lain.
            $table->foreignId('tenant_order_id')->constrained()->restrictOnDelete();

            $table->string('payment_reference', 100);
            $table->unsignedBigInteger('amount');

            // TODO: isi daftar status sesuai alur bisnis kamu (ERD tidak merinci nilainya)
            $table->enum('status', ['pending', 'processing', 'paid', 'failed', 'refunded', 'cancelled'])
                ->default('pending');

            $table->timestamp('settled_at', 6)->nullable();
            $table->string('idempotency_key', 100);
            $table->timestamps(6);

            $table->unique(['tenant_order_id', 'payment_reference']);
            $table->unique(['idempotency_key']);
        });

        Schema::create('payment_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained()->restrictOnDelete();
            $table->string('provider_reference', 100);
            $table->json('qris_payload')->nullable();
            $table->timestamp('expires_at', 6)->nullable();

            // TODO: isi daftar status sesuai alur attempt kamu
            $table->enum('status', ['initiated', 'pending', 'succeeded', 'failed', 'expired'])
                ->default('initiated');

            $table->timestamps(6);

            $table->unique(['provider_reference']);
        });

        // Append-only: tidak ada updated_at
        Schema::create('payment_events', function (Blueprint $table): void {
            $table->id();

            // TODO: konfirmasi ke ERD — FK ini ke payments atau payment_attempts?
            $table->foreignId('payment_id')->constrained()->restrictOnDelete();

            $table->string('provider_event_id', 100);
            $table->string('signature', 255);
            $table->json('payload');

            // TODO: tentukan tipe result — enum atau string bebas dari provider
            $table->string('result', 50);

            $table->timestamp('occurred_at', 6)->useCurrent();

            $table->unique(['provider_event_id']);
        });

        Schema::create('withdrawals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();

            // TODO: cek nama tabel rekening bank kamu persis (tenant_bank_accounts?)
            $table->foreignId('bank_account_id')->constrained('tenant_bank_accounts')->restrictOnDelete();

            $table->foreignId('requester_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();

            $table->unsignedBigInteger('amount');

            // TODO: isi daftar status sesuai alur approval withdrawal kamu
            $table->enum('status', ['requested', 'processing', 'completed', 'rejected'])
                ->default('requested');

            $table->json('transfer_snapshot')->nullable();
            $table->string('idempotency_key', 100);
            $table->timestamps(6);

            $table->unique(['idempotency_key']);
            // TODO: "UQ active tenant" — constraint bersyarat (hanya 1 withdrawal aktif
            // per tenant) tidak bisa langsung pakai unique() biasa. Validasi ini sebaiknya
            // dilakukan di Model/Service sebelum insert, bukan di level migration.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('payment_attempts');
        Schema::dropIfExists('payments');
    }
};
