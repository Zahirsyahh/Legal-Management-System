<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ubah enum status → tambahkan 'declined' dan 'skipped'
        DB::statement("
            ALTER TABLE contract_departments
            MODIFY COLUMN status ENUM(
                'pending_assignment',
                'assigned',
                'in_progress',
                'completed',
                'declined',
                'skipped'
            ) NOT NULL DEFAULT 'pending_assignment'
        ");

        Schema::table('contract_departments', function (Blueprint $table) {
            // 2. Tambah kolom notes jika belum ada
            if (!Schema::hasColumn('contract_departments', 'notes')) {
                $table->text('notes')->nullable()->after('completed_at');
            }

            // 3. Tambah kolom started_at jika belum ada
            if (!Schema::hasColumn('contract_departments', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('assigned_at');
            }
        });
    }

    public function down(): void
    {
        // Kembalikan enum ke versi awal
        DB::statement("
            ALTER TABLE contract_departments
            MODIFY COLUMN status ENUM(
                'pending_assignment',
                'assigned',
                'completed'
            ) NOT NULL DEFAULT 'pending_assignment'
        ");

        Schema::table('contract_departments', function (Blueprint $table) {
            if (Schema::hasColumn('contract_departments', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('contract_departments', 'started_at')) {
                $table->dropColumn('started_at');
            }
        });
    }
};