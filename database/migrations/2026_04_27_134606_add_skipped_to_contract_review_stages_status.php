<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE contract_review_stages
            MODIFY COLUMN status ENUM(
                'pending',
                'assigned',
                'in_progress',
                'completed',
                'revision_requested',
                'rejected',
                'skipped'
            ) NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE contract_review_stages
            MODIFY COLUMN status ENUM(
                'pending',
                'assigned',
                'in_progress',
                'completed',
                'revision_requested',
                'rejected'
            ) NOT NULL DEFAULT 'pending'
        ");
    }
};