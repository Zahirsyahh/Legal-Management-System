<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_departments', function (Blueprint $table) {
            $table->timestamp('declined_at')->nullable()->after('completed_at');

            // ✅ unsignedInteger() = int(10) unsigned — matches tbl_user.id_user exactly
            $table->unsignedInteger('declined_by')->nullable()->after('declined_at');

            $table->foreign('declined_by')
                ->references('id_user')
                ->on('tbl_user')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contract_departments', function (Blueprint $table) {
            $table->dropForeign(['declined_by']);
            $table->dropColumn(['declined_at', 'declined_by']);
        });
    }
};