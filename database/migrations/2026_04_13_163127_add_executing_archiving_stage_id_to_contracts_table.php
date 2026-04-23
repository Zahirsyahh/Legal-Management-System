<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom jika belum ada
        if (!Schema::hasColumn('contracts', 'executing_stage_id')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->unsignedBigInteger('executing_stage_id')
                      ->nullable()
                      ->after('final_approved_by');
            });
        }

        if (!Schema::hasColumn('contracts', 'archiving_stage_id')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->unsignedBigInteger('archiving_stage_id')
                      ->nullable()
                      ->after('executing_stage_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {

            if (Schema::hasColumn('contracts', 'executing_stage_id')) {
                $table->dropColumn('executing_stage_id');
            }

            if (Schema::hasColumn('contracts', 'archiving_stage_id')) {
                $table->dropColumn('archiving_stage_id');
            }

        });
    }
};
