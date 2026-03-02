<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/[timestamp]_add_action_fields_to_review_stages_table.php
    public function up(){
    Schema::table('contract_review_stages', function (Blueprint $table) {
        if (!Schema::hasColumn('contract_review_stages', 'action_notes')) {
            $table->text('action_notes')->nullable();
        }

        if (!Schema::hasColumn('contract_review_stages', 'jump_to_stage_id')) {
            $table->foreignId('jump_to_stage_id')
                ->nullable()
                ->constrained('contract_review_stages')
                ->nullOnDelete();
        }
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
