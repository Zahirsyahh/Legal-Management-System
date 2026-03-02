<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contract_review_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('contract_id');
            $table->unsignedBigInteger('stage_id')->nullable();
            $table->unsignedBigInteger('user_id');

            $table->enum('action', [
                'approve',
                'approve_jump',
                'revision',
                'reject',
            ]);

            $table->text('notes')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('contract_id')
                ->references('id')->on('contracts')
                ->cascadeOnDelete();

            $table->foreign('stage_id')
                ->references('id')->on('contract_review_stages')
                ->nullOnDelete();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();

            // Indexes
            $table->index(['contract_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_review_logs');
    }
};
