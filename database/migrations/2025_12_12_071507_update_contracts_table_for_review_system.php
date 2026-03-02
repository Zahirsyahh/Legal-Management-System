// database/migrations/xxxx_update_contracts_table_for_review_system.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Jika belum ada kolom-kolom ini
            if (!Schema::hasColumn('contracts', 'current_stage')) {
                $table->integer('current_stage')->default(1);
            }
            
            if (!Schema::hasColumn('contracts', 'review_flow_status')) {
                $table->enum('review_flow_status', [
                    'pending_assignment',
                    'in_review',
                    'completed',
                    'revision_requested',
                    'rejected'
                ])->default('pending_assignment');
            }
            
            if (!Schema::hasColumn('contracts', 'legal_status')) {
                $table->enum('legal_status', [
                    'pending',
                    'assigned', 
                    'under_review',
                    'completed',
                    'revision_requested'
                ])->default('pending');
            }
            
            if (!Schema::hasColumn('contracts', 'legal_review_started_at')) {
                $table->timestamp('legal_review_started_at')->nullable();
            }
            
            if (!Schema::hasColumn('contracts', 'legal_review_completed_at')) {
                $table->timestamp('legal_review_completed_at')->nullable();
            }
            
            if (!Schema::hasColumn('contracts', 'legal_notes')) {
                $table->text('legal_notes')->nullable();
            }
            
            if (!Schema::hasColumn('contracts', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }
            
            if (!Schema::hasColumn('contracts', 'legal_reviewed_at')) {
                $table->timestamp('legal_reviewed_at')->nullable();
            }
            
            if (!Schema::hasColumn('contracts', 'legal_feedback')) {
                $table->text('legal_feedback')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Hapus kolom jika perlu rollback
            $columns = [
                'current_stage',
                'review_flow_status',
                'legal_status',
                'legal_review_started_at',
                'legal_review_completed_at',
                'legal_notes',
                'submitted_at',
                'legal_reviewed_at',
                'legal_feedback'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('contracts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};