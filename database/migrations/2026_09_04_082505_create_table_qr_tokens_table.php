<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_qr_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dining_table_id')->constrained()->cascadeOnDelete();
            $table->binary('token_hash', 32);
            $table->enum('status', ['active', 'revoked'])->default('active');
            $table->timestamp('issued_at', 6)->useCurrent();
            $table->timestamp('expires_at', 6)->nullable();
            $table->timestamp('revoked_at', 6)->nullable();
            $table->timestamps(6);

            $table->unique('token_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_qr_tokens');
    }
};