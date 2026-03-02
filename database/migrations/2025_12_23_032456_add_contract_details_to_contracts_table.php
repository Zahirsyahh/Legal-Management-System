<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {

            if (!Schema::hasColumn('contracts', 'contract_type')) {
                $table->string('contract_type')->nullable()->after('title');
            }

            if (!Schema::hasColumn('contracts', 'counterparty_name')) {
                $table->string('counterparty_name')->nullable()->after('description');
            }

            if (!Schema::hasColumn('contracts', 'counterparty_email')) {
                $table->string('counterparty_email')->nullable()->after('counterparty_name');
            }

            if (!Schema::hasColumn('contracts', 'counterparty_phone')) {
                $table->string('counterparty_phone')->nullable()->after('counterparty_email');
            }

            if (!Schema::hasColumn('contracts', 'effective_date')) {
                $table->date('effective_date')->nullable()->after('counterparty_phone');
            }

            if (!Schema::hasColumn('contracts', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('effective_date');
            }

            if (!Schema::hasColumn('contracts', 'contract_value')) {
                $table->decimal('contract_value', 15, 2)->nullable()->after('expiry_date');
            }

            if (!Schema::hasColumn('contracts', 'currency')) {
                $table->string('currency', 3)->default('IDR')->after('contract_value');
            }

            if (!Schema::hasColumn('contracts', 'additional_notes')) {
                $table->text('additional_notes')->nullable()->after('currency');
            }

            // ✅ TIDAK ADA current_stage DI SINI

            if (!Schema::hasColumn('contracts', 'review_flow_status')) {
                $table->string('review_flow_status')->nullable();
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

            if (!Schema::hasColumn('contracts', 'legal_status')) {
                $table->string('legal_status')->nullable();
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

            $columns = [
                'contract_type',
                'counterparty_name',
                'counterparty_email',
                'counterparty_phone',
                'effective_date',
                'expiry_date',
                'contract_value',
                'currency',
                'additional_notes',
                'review_flow_status',
                'legal_review_started_at',
                'legal_review_completed_at',
                'legal_notes',
                'legal_status',
                'submitted_at',
                'legal_reviewed_at',
                'legal_feedback',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('contracts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
