<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE contract_review_stages 
            MODIFY status ENUM(
                'pending',
                'assigned',
                'in_progress',
                'completed',
                'revision_requested',
                'rejected',
                'skipped',
                'declined'
            ) NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE contract_review_stages 
            MODIFY status ENUM(
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
};
