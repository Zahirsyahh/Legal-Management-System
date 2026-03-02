// database/migrations/2024_01_05_create_contract_departments_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained();
            
            $table->enum('status', [
                'pending_assignment',  // Menunggu admin assign reviewers
                'assigned',            // Reviewers sudah di-assign
                'in_progress',         // Sedang diproses
                'completed',           // Selesai review
                'not_required',        // Tidak dipilih
                'cancelled'           // Dibatalkan
            ])->default('pending_assignment');
            
            $table->foreignId('assigned_admin_id')->nullable()->constrained('users');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Unique constraint: satu kontrak hanya bisa punya satu entry per department
            $table->unique(['contract_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_departments');
    }
};