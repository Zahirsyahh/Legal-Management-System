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
    Schema::table('contract_review_stages', function (Blueprint $table) {
        $table->timestamp('visited_at')->nullable()->after('assigned_at');
        $table->foreignId('visited_by')->nullable()->after('visited_at');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_review_stages', function (Blueprint $table) {
            //
        });
    }
};
