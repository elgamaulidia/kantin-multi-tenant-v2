<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modul 7 Tahap 3: pivot menu <-> grup modifier hanya sah bila menu dan grup
 * milik tenant yang sama (composite FK ke unique (tenant_id, id)).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_modifier_groups', function (Blueprint $table): void {
            $table->foreign(['tenant_id', 'menu_id'], 'mmg_tenant_menu_foreign')
                ->references(['tenant_id', 'id'])->on('menus')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'modifier_group_id'], 'mmg_tenant_group_foreign')
                ->references(['tenant_id', 'id'])->on('modifier_groups')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('menu_modifier_groups', function (Blueprint $table): void {
            $table->dropForeign('mmg_tenant_menu_foreign');
            $table->dropForeign('mmg_tenant_group_foreign');
        });
    }
};
