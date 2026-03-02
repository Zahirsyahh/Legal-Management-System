<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================
        // 1. CREATE DEPARTMENTS TABLE (jika belum ada)
        // ============================================
        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Legal, Finance, Accounting, Tax
                $table->string('code')->unique(); // LEGAL, FIN, ACC, TAX
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // ============================================
        // 2. ADD COLUMNS TO CONTRACT_REVIEW_STAGES
        // ============================================
        if (Schema::hasTable('contract_review_stages')) {
            // Cek dulu kolom sudah ada atau belum
            if (!Schema::hasColumn('contract_review_stages', 'department_id')) {
                Schema::table('contract_review_stages', function (Blueprint $table) {
                    // Link ke department
                    $table->foreignId('department_id')->nullable()->after('contract_id');
                    
                    // Untuk dynamic stage management
                    $table->foreignId('created_by')->nullable()->after('assigned_user_id')->constrained('users');
                    $table->boolean('is_manual_added')->default(false)->after('created_by');
                    $table->text('add_reason')->nullable()->after('is_manual_added');
                });
                
                // Add foreign key constraint
                Schema::table('contract_review_stages', function (Blueprint $table) {
                    $table->foreign('department_id')
                          ->references('id')
                          ->on('departments')
                          ->onDelete('set null');
                });
            }
        }

        // ============================================
        // 3. ADD COLUMNS TO CONTRACTS TABLE
        // ============================================
        if (Schema::hasTable('contracts')) {
            // Cek dulu kolom sudah ada atau belum
            if (!Schema::hasColumn('contracts', 'selected_departments')) {
                Schema::table('contracts', function (Blueprint $table) {
                    // Untuk multi-department
                    $table->json('selected_departments')->nullable()->after('status');
                    
                    // Untuk dynamic workflow
                    $table->boolean('allow_stage_addition')->default(true)->after('selected_departments');
                });
            }
        }

        // ============================================
        // 4. CREATE CONTRACT_DEPARTMENTS TABLE
        // ============================================
        if (!Schema::hasTable('contract_departments')) {
            Schema::create('contract_departments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('contract_id')->constrained()->onDelete('cascade');
                $table->foreignId('department_id')->constrained();
                $table->enum('status', ['pending_assignment', 'assigned', 'completed'])->default('pending_assignment');
                $table->foreignId('assigned_admin_id')->nullable()->constrained('users');
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                
                $table->unique(['contract_id', 'department_id']);
            });
        }
    }

    public function down(): void
    {
        // HAPUS TABEL BARU SAJA (tidak hapus yang lama)
        Schema::dropIfExists('contract_departments');
        
        // HAPUS KOLOM YANG DITAMBAHKAN (opsional, hati-hati!)
        if (Schema::hasTable('contract_review_stages')) {
            Schema::table('contract_review_stages', function (Blueprint $table) {
                if (Schema::hasColumn('contract_review_stages', 'department_id')) {
                    $table->dropForeign(['department_id']);
                    $table->dropColumn(['department_id', 'created_by', 'is_manual_added', 'add_reason']);
                }
            });
        }
        
        if (Schema::hasTable('contracts')) {
            Schema::table('contracts', function (Blueprint $table) {
                if (Schema::hasColumn('contracts', 'selected_departments')) {
                    $table->dropColumn(['selected_departments', 'allow_stage_addition']);
                }
            });
        }
        
        // HAPUS DEPARTMENTS TABLE (jika mau)
        // Schema::dropIfExists('departments');
    }
};