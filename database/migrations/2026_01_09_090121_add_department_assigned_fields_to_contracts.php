<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            // ====================
            // DEPARTMENT ASSIGNED IDS
            // ====================
            $table->foreignId('finance_assigned_id')
                  ->nullable()
                  ->after('legal_assigned_id')
                  ->constrained('users')
                  ->nullOnDelete();
                  
            $table->foreignId('accounting_assigned_id')
                  ->nullable()
                  ->after('finance_assigned_id')
                  ->constrained('users')
                  ->nullOnDelete();
                  
            $table->foreignId('tax_assigned_id')
                  ->nullable()
                  ->after('accounting_assigned_id')
                  ->constrained('users')
                  ->nullOnDelete();
            
            // ====================
            // FINANCE DEPARTMENT FIELDS
            // ====================
            $table->timestamp('finance_review_started_at')->nullable()->after('tax_assigned_id');
            $table->timestamp('finance_review_completed_at')->nullable()->after('finance_review_started_at');
            $table->text('finance_feedback')->nullable()->after('finance_review_completed_at');
            
            // ====================
            // ACCOUNTING DEPARTMENT FIELDS
            // ====================
            $table->timestamp('accounting_review_started_at')->nullable()->after('finance_feedback');
            $table->timestamp('accounting_review_completed_at')->nullable()->after('accounting_review_started_at');
            $table->text('accounting_feedback')->nullable()->after('accounting_review_completed_at');
            
            // ====================
            // TAX DEPARTMENT FIELDS
            // ====================
            $table->timestamp('tax_review_started_at')->nullable()->after('accounting_feedback');
            $table->timestamp('tax_review_completed_at')->nullable()->after('tax_review_started_at');
            $table->text('tax_feedback')->nullable()->after('tax_review_completed_at');
            
            // ====================
            // STATUS FIELDS (Optional - untuk dashboard)
            // ====================
            $table->enum('finance_status', ['pending', 'approved', 'revision_requested'])
                  ->default('pending')
                  ->after('tax_feedback');
                  
            $table->enum('accounting_status', ['pending', 'approved', 'revision_requested'])
                  ->default('pending')
                  ->after('finance_status');
                  
            $table->enum('tax_status', ['pending', 'approved', 'revision_requested'])
                  ->default('pending')
                  ->after('accounting_status');
        });
    }

    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['finance_assigned_id']);
            $table->dropForeign(['accounting_assigned_id']);
            $table->dropForeign(['tax_assigned_id']);
            
            // Drop columns
            $table->dropColumn([
                'finance_assigned_id',
                'accounting_assigned_id',
                'tax_assigned_id',
                'finance_review_started_at',
                'finance_review_completed_at',
                'finance_feedback',
                'accounting_review_started_at',
                'accounting_review_completed_at',
                'accounting_feedback',
                'tax_review_started_at',
                'tax_review_completed_at',
                'tax_feedback',
                'finance_status',
                'accounting_status',
                'tax_status',
            ]);
        });
    }
};