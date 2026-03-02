<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================
        // 1. DROP TABEL TERKAIT (AMAN TANPA DOCTRINE)
        // ============================================
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('contract_departments');
        Schema::dropIfExists('department_workflow_templates');
        Schema::dropIfExists('departments');

        Schema::enableForeignKeyConstraints();

        // ============================================
        // 2. RECREATE DEPARTMENTS
        // ============================================
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ============================================
        // 3. SEED DATA DEFAULT
        // ============================================
        DB::table('departments')->insert([
            [
                'name' => 'Legal',
                'code' => 'LEGAL',
                'description' => 'Legal Department - Contract Review & Compliance',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Finance',
                'code' => 'FIN',
                'description' => 'Finance Department - Budget & Financial Approval',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Accounting',
                'code' => 'ACC',
                'description' => 'Accounting Department - Booking & Payment Verification',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tax',
                'code' => 'TAX',
                'description' => 'Tax Department - Tax Implications Review',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // ============================================
        // 4. CONTRACT_DEPARTMENTS
        // ============================================
        Schema::create('contract_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending_assignment', 'assigned', 'completed'])
                  ->default('pending_assignment');
            $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['contract_id', 'department_id']);
        });

        // ============================================
        // 5. CONTRACT_REVIEW_STAGES
        // ============================================
        if (Schema::hasTable('contract_review_stages')
            && !Schema::hasColumn('contract_review_stages', 'department_id')) {

            Schema::table('contract_review_stages', function (Blueprint $table) {
                $table->foreignId('department_id')
                      ->nullable()
                      ->constrained('departments')
                      ->nullOnDelete()
                      ->after('contract_id');
            });
        }

        // ============================================
        // 6. CONTRACTS
        // ============================================
        if (Schema::hasTable('contracts')
            && !Schema::hasColumn('contracts', 'selected_departments')) {

            Schema::table('contracts', function (Blueprint $table) {
                $table->json('selected_departments')->nullable()->after('status');
                $table->boolean('allow_stage_addition')->default(true)->after('selected_departments');
            });
        }
    }

    public function down(): void
    {
        // intentionally minimal (safe rollback)
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('contract_departments');
        Schema::enableForeignKeyConstraints();
    }
};
