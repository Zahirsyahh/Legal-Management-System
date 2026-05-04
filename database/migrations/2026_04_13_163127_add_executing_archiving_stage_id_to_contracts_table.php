<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('executing_stage_id')
                  ->nullable()
                  ->after('final_approved_by');

            $table->unsignedBigInteger('archiving_stage_id')
                  ->nullable()
                  ->after('executing_stage_id');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'executing_stage_id',
                'archiving_stage_id',
            ]);
        });
    }
};
