<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_revision_tasks', function (Blueprint $table) {
            $table->id();

            // Relasi ke contract & stage pemberi revisi
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->unsignedBigInteger('from_stage_id');   // stage milik Bimo (pemberi revisi)
            $table->foreign('from_stage_id')->references('id')->on('contract_review_stages')->cascadeOnDelete();

            // Target penerima revisi
            $table->unsignedBigInteger('to_stage_id');     // stage milik Bunga/Sari (penerima revisi)
            $table->foreign('to_stage_id')->references('id')->on('contract_review_stages')->cascadeOnDelete();

            // Siapa yang mengirim revisi
            $table->unsignedInteger('requested_by'); 
            $table->foreign('requested_by')->references('id_user')->on('tbl_user');

            // Siapa yang menerima revisi
            $table->unsignedInteger('assigned_to');
            $table->foreign('assigned_to')->references('id_user')->on('tbl_user');

            // Konten revisi
            $table->text('revision_notes');                // catatan dari Bimo ke penerima
            $table->text('response_notes')->nullable();    // balasan dari Bunga/Sari

            /**
             * Status lifecycle revision task:
             *   pending         → baru dikirim, belum ditanggapi penerima
             *   in_progress     → penerima sedang mengerjakan
             *   submitted       → penerima sudah submit kembali ke Bimo
             *   approved        → Bimo setujui hasil revisi Bunga/Sari
             *   re_requested    → Bimo kirim balik revisi lagi ke Bunga/Sari
             *   cancelled       → dibatalkan (misal: stage selesai, task tidak relevan)
             */
            $table->enum('status', [
                'pending',
                'in_progress',
                'submitted',
                'approved',
                're_requested',
                'cancelled',
            ])->default('pending');

            // Loop counter — berapa kali revisi bolak-balik untuk task ini
            $table->unsignedTinyInteger('loop_count')->default(0);

            // Timestamps aksi penting
            $table->timestamp('sent_at')->nullable();        // kapan Bimo kirim revisi
            $table->timestamp('started_at')->nullable();     // kapan penerima mulai kerjakan
            $table->timestamp('submitted_at')->nullable();   // kapan penerima submit balik
            $table->timestamp('approved_at')->nullable();    // kapan Bimo approve task ini
            $table->timestamp('cancelled_at')->nullable();

            // Parent task — untuk melacak re-request chain
            $table->unsignedBigInteger('parent_task_id')->nullable();
            $table->foreign('parent_task_id')->references('id')->on('contract_revision_tasks')->nullOnDelete();

            $table->timestamps();

            // Index untuk query performa
            $table->index(['contract_id', 'from_stage_id', 'status']);
            $table->index(['contract_id', 'to_stage_id', 'status']);
            $table->index(['assigned_to', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_revision_tasks');
    }
};