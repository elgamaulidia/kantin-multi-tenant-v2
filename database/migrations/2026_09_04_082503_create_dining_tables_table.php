<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dining_tables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('canteen_id')->constrained()->restrictOnDelete();
            $table->string('code', 30);
            $table->string('label', 60);
            $table->string('zone', 60)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps(6);

            $table->unique(['canteen_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dining_tables');
    }
};
