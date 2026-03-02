<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contract_review_logs', function (Blueprint $table) {
            // Tambahkan description jika belum ada
            if (!Schema::hasColumn('contract_review_logs', 'description')) {
                $table->text('description')->nullable()->after('action');
            }
            
            // Tambahkan metadata jika belum ada
            if (!Schema::hasColumn('contract_review_logs', 'metadata')) {
                $table->json('metadata')->nullable()->after('notes');
            }
        });
    }

    public function down()
    {
        Schema::table('contract_review_logs', function (Blueprint $table) {
            $table->dropColumn(['description', 'metadata']);
        });
    }
};