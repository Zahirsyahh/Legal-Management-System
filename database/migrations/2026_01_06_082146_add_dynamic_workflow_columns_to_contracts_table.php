// database/migrations/2024_01_03_add_dynamic_workflow_columns_to_contracts_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Untuk track department yang dipilih saat start review
            $table->json('selected_departments')->nullable()->after('status');
            
            // Untuk dynamic workflow control
            $table->enum('workflow_type', ['single', 'multi'])->default('single')->after('selected_departments');
            $table->boolean('allow_stage_addition')->default(true)->after('workflow_type');
            
            // Untuk multi-department tracking
            $table->enum('multi_department_status', [
                'single_department',
                'legal_completed',
                'multi_department',
                'all_departments_done'
            ])->default('single_department')->after('allow_stage_addition');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'selected_departments',
                'workflow_type',
                'allow_stage_addition',
                'multi_department_status'
            ]);
        });
    }
};