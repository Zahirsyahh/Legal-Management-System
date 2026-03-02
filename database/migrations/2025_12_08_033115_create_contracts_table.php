<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();

            // BASIC INFO
            $table->string('contract_number')->nullable()->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('purpose')->nullable();

            // STATUS (WORKFLOW)
            $table->enum('status', [
                'draft',
                'submitted',
                'awaiting_document_upload',
                'document_uploaded',
                'user_reviewing',
                'user_review_complete',
                'legal_reviewing_feedback',
                'legal_approved',
                'final_approved',
                'revision_needed',
                'declined',
                'cancelled',
                'under_review',
            ])->default('draft');

            // SYNOLOGY METADATA
            $table->string('synology_folder_path')->nullable();
            $table->timestamp('document_uploaded_at')->nullable();
            $table->foreignId('document_uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            // USER REVIEW
            $table->timestamp('user_review_started_at')->nullable();
            $table->timestamp('user_review_completed_at')->nullable();
            $table->text('user_feedback')->nullable();

            // APPROVALS
            $table->timestamp('legal_approved_at')->nullable();
            $table->foreignId('legal_approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('final_approved_at')->nullable();
            $table->foreignId('final_approved_by')->nullable()->constrained('users')->nullOnDelete();

            // RELATIONSHIPS
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('legal_assigned_id')->nullable()->constrained('users')->nullOnDelete();
            
            // SYSTEM
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
