<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // FR-TEN-01 / UC-19 — role menentukan konteks dashboard (tenant/admin)
            $table->enum('role', ['tenant', 'admin'])->nullable()->after('email');

            // Relasi ke tenant (nullable, karena admin tidak terikat satu tenant)
            // Tabel `tenants` belum dibuat di Modul 2 ini — kolom disiapkan dulu
            // tanpa foreign key constraint, akan ditambahkan saat tabel tenants ada.
            $table->unsignedBigInteger('tenant_id')->nullable()->after('role');

            // Status akun (SRS UC-19: akun dapat dikunci sementara)
            $table->string('account_status')->default('active')->after('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'tenant_id', 'account_status']);
        });
    }
};
