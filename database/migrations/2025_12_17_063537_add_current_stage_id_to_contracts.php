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
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('current_stage_id')
                ->nullable()
                ->after('current_stage')
                ->constrained('contract_review_stages')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('contracts', function (Blueprint $table) {
        $table->dropColumn('current_stage_id');
    });
}

};
