<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_review_stages', function (Blueprint $table) {

            if (!Schema::hasColumn('contract_review_stages', 'needs_revision')) {
                $table->boolean('needs_revision')
                      ->default(false)
                      ->after('status');
            }

            if (!Schema::hasColumn('contract_review_stages', 'revision_feedback')) {
                $table->text('revision_feedback')
                      ->nullable()
                      ->after('needs_revision');
            }

            if (!Schema::hasColumn('contract_review_stages', 'revision_requested_at')) {
                $table->timestamp('revision_requested_at')
                      ->nullable()
                      ->after('revision_feedback');
            }

            if (!Schema::hasColumn('contract_review_stages', 'revision_requested_by')) {
                $table->unsignedBigInteger('revision_requested_by')
                      ->nullable()
                      ->after('revision_requested_at')
                      ->comment('user.id who requested revision');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contract_review_stages', function (Blueprint $table) {

            $columns = [
                'needs_revision',
                'revision_feedback',
                'revision_requested_at',
                'revision_requested_by',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('contract_review_stages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
