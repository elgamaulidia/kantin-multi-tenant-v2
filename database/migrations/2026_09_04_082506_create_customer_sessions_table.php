<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_sessions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignId('canteen_id')->constrained()->restrictOnDelete();
            $table->foreignId('dining_table_id')->constrained()->restrictOnDelete();
            $table->foreignId('table_qr_token_id')->constrained()->restrictOnDelete();
            $table->binary('session_token_hash', 32);
            $table->enum('status', ['active', 'expired'])->default('active');
            $table->timestamp('expires_at', 6);
            $table->timestamps(6);

            $table->unique('session_token_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_sessions');
    }
};
