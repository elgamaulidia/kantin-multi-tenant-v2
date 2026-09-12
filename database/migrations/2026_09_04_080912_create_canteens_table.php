<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canteens', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('slug', 100)->unique();
            $table->string('name', 120);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('service_fee_rate', 5, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps(6);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canteens');
    }
};