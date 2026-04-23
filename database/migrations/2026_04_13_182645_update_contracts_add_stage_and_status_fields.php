<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Pastikan enum status punya 'declined' ──
        $currentEnum = DB::select("SHOW COLUMNS FROM contracts WHERE Field = 'status'");

        if ($currentEnum) {
            $enumDef = $currentEnum[0]->Type ?? '';

            if (!str_contains($enumDef, "'declined'")) {
                DB::statement("
                    ALTER TABLE contracts MODIFY COLUMN status ENUM(
                        'draft','submitted','awaiting_document_upload','document_uploaded',
                        'user_reviewing','user_review_complete','legal_reviewing_feedback',
                        'legal_reviewing','legal_approved','fat_reviewing','fat_review_complete',
                        'fat_approved','final_approved','revision_needed','declined','cancelled',
                        'under_review','number_issued','released','executed','archived'
                    ) NULL
                ");
            }
        }

        Schema::table('contracts', function (Blueprint $table) {

            // ── 2. executing_stage_id ──
            if (!Schema::hasColumn('contracts', 'executing_stage_id')) {
                $table->unsignedBigInteger('executing_stage_id')
                      ->nullable()
                      ->after('final_approved_by');
            }

            // ── 3. archiving_stage_id ──
            if (!Schema::hasColumn('contracts', 'archiving_stage_id')) {
                $table->unsignedBigInteger('archiving_stage_id')
                      ->nullable()
                      ->after('executing_stage_id');
            }

            // ── 4. multi_department_status ──
            if (!Schema::hasColumn('contracts', 'multi_department_status')) {
                $table->enum('multi_department_status', [
                        'single_department',
                        'legal_completed',
                        'multi_department',
                        'all_departments_done',
                    ])
                    ->default('single_department')
                    ->after('selected_departments');
            }

            // ── 5. surat_file_mime ──
            if (!Schema::hasColumn('contracts', 'surat_file_mime')) {
                $table->string('surat_file_mime', 100)
                      ->nullable()
                      ->after('surat_file_size');
            }
        });
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

            if (Schema::hasColumn('contracts', 'multi_department_status')) {
                $table->dropColumn('multi_department_status');
            }

            if (Schema::hasColumn('contracts', 'surat_file_mime')) {
                $table->dropColumn('surat_file_mime');
            }
        });
    }
};
