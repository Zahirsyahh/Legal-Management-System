<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {

            // Timestamps untuk tracking
            $table->timestamp('submitted_at')->nullable()->after('fat_assigned_id');
            $table->timestamp('legal_reviewed_at')->nullable()->after('submitted_at');
            $table->timestamp('fat_reviewed_at')->nullable()->after('legal_reviewed_at');
            $table->timestamp('approved_at')->nullable()->after('fat_reviewed_at');
            $table->timestamp('released_at')->nullable()->after('approved_at');

            // Soft deletes jika belum ada
            if (!Schema::hasColumn('contracts', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {

            // Hapus kolom timestamp
            $table->dropColumn([
                'submitted_at',
                'legal_reviewed_at',
                'fat_reviewed_at',
                'approved_at',
                'released_at',
            ]);

            // Hapus soft deletes jika ada
            if (Schema::hasColumn('contracts', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
