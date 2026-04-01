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
        Schema::create('archives', function (Blueprint $table) {

            $table->id();

            $table->string('record_id', 100)->nullable();
            $table->string('doc_number', 100)->nullable();
            $table->string('doc_name', 255);

            // Department code (LG, HR, OP, dll)
            $table->string('department_code', 5);

            $table->string('counterparty', 255)->nullable();
            $table->text('description')->nullable();

            $table->enum('doc_status', [
                'copy',
                'scancopy',
                'hardcopy',
                'born_digital'
            ]);

            $table->enum('version_status', [
                'latest',
                'obsolete',
                'superseded',
            ])->default('active');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->string('doc_location', 255)->nullable();
            $table->string('synology_path', 255)->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            // index untuk performa
            $table->index('department_code');
            $table->index('version_status');
            $table->index('start_date');
            $table->index('end_date');

            // optional foreign key (kalau tbl_user kamu pakai id_user)
            // $table->foreign('created_by')->references('id_user')->on('tbl_users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archives');
    }
};
