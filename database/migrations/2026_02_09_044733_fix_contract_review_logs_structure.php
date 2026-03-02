<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Drop foreign key lama jika ada
        Schema::table('contract_review_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // 2. Ubah struktur kolom
        DB::statement("ALTER TABLE contract_review_logs 
            MODIFY COLUMN `action` VARCHAR(100) NOT NULL COMMENT 'Action type: workflow_started, stage_created, etc.'");
        
        DB::statement("ALTER TABLE contract_review_logs 
            MODIFY COLUMN `user_id` INT UNSIGNED NOT NULL");

        // 3. Tambah kolom baru jika belum ada
        Schema::table('contract_review_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('contract_review_logs', 'description')) {
                $table->text('description')->nullable()->after('action')
                    ->comment('Human-readable action description');
            }
            
            if (!Schema::hasColumn('contract_review_logs', 'metadata')) {
                $table->json('metadata')->nullable()->after('notes')
                    ->comment('Additional action metadata');
            }
        });

        // 4. Buat foreign key baru yang benar
        Schema::table('contract_review_logs', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id_user')->on('tbl_user')
                ->onDelete('cascade');
        });

        // 5. Tambah indexes
        Schema::table('contract_review_logs', function (Blueprint $table) {
            $table->index(['stage_id', 'created_at'], 'idx_stage_created');
            $table->index(['user_id', 'created_at'], 'idx_user_created');
            $table->index(['action', 'created_at'], 'idx_action_created');
            $table->index('created_at', 'idx_created_at');
        });
    }

    public function down(): void
    {
        Schema::table('contract_review_logs', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('idx_stage_created');
            $table->dropIndex('idx_user_created');
            $table->dropIndex('idx_action_created');
            $table->dropIndex('idx_created_at');
            
            // Drop foreign key
            $table->dropForeign(['user_id']);
            
            // Drop columns
            $table->dropColumn(['description', 'metadata']);
        });

        // Revert action to ENUM
        DB::statement("ALTER TABLE contract_review_logs 
            MODIFY COLUMN `action` ENUM('approve', 'approve_jump', 'revision', 'reject') NOT NULL");
        
        // Revert user_id type
        DB::statement("ALTER TABLE contract_review_logs 
            MODIFY COLUMN `user_id` BIGINT UNSIGNED NOT NULL");

        // Recreate old foreign key
        Schema::table('contract_review_logs', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }
};