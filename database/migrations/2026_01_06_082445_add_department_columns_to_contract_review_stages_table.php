// database/migrations/2024_01_04_add_department_columns_to_contract_review_stages_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_review_stages', function (Blueprint $table) {
            // Link ke department
            $table->foreignId('department_id')->nullable()->after('contract_id');
            
            // Untuk dynamic stage management
            $table->string('stage_key')->nullable()->after('stage_name');
            $table->boolean('is_department_final')->default(false)->after('stage_type');
            
            // Untuk manual stage addition
            $table->foreignId('created_by')->nullable()->after('assigned_user_id')->constrained('users');
            $table->boolean('is_manual_added')->default(false)->after('created_by');
            $table->text('add_reason')->nullable()->after('is_manual_added');
            $table->integer('original_sequence')->nullable()->after('sequence');
            
            // Indexes untuk performance
            $table->index(['contract_id', 'department_id']);

        });
        
        // Add foreign key constraint (set null on delete)
        Schema::table('contract_review_stages', function (Blueprint $table) {
            $table->foreign('department_id')
                  ->references('id')
                  ->on('departments')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('contract_review_stages', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn([
                'department_id',
                'stage_key',
                'is_department_final',
                'created_by',
                'is_manual_added',
                'add_reason',
                'original_sequence'
            ]);
        });
    }
};