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
        Schema::create('contract_review_stages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('contract_id')->constrained()->onDelete('cascade');
        $table->string('stage_name'); // legal_1, legal_2, admin_legal, fat_1, fat_2
        $table->string('stage_type'); // legal, admin_legal, fat
        $table->foreignId('assigned_user_id')->nullable()->constrained('users');
        $table->integer('sequence'); // 1, 2, 3, 4, 5
        $table->enum('status', [
            'pending', 
            'assigned',
            'in_progress', 
            'completed', 
            'revision_requested',
            'rejected'
        ])->default('pending');
        $table->text('notes')->nullable();
        $table->timestamp('assigned_at')->nullable();
        $table->timestamp('started_at')->nullable();
        $table->timestamp('completed_at')->nullable();
        $table->timestamps();
        
        $table->unique(['contract_id', 'stage_name']);
        $table->index(['contract_id', 'sequence']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_review_stages');
    }
};
