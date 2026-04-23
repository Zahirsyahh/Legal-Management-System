<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Reference stage
            $table->unsignedBigInteger('executing_stage_id')->nullable()->after('final_approved_by');
            $table->unsignedBigInteger('archiving_stage_id')->nullable()->after('executing_stage_id');

            // Executing fields
            $table->string('executed_file')->nullable()->after('executed_by');
            $table->text('execution_notes')->nullable()->after('executed_file');

            // Archiving fields
            $table->string('archive_location')->nullable()->after('archived_by');
            $table->text('archive_notes')->nullable()->after('archive_location');
            $table->string('archive_file')->nullable()->after('archive_notes');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'executing_stage_id',
                'archiving_stage_id',
                'executed_file',
                'execution_notes',
                'archive_location',
                'archive_notes',
                'archive_file'
            ]);
        });
    }
};
