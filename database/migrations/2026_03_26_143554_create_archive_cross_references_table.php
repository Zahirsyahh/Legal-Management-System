<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archive_cross_references', function (Blueprint $table) {

            $table->id();

            $table->foreignId('archive_id')
                  ->constrained('archives')
                  ->onDelete('cascade');

            $table->string('ref_doc_name', 255);
            $table->string('ref_record_id', 100)->nullable();
            $table->string('ref_location', 255)->nullable();

            $table->enum('ref_relation', [
                'reference',
                'amendment',
                'related',
                'supporting'
            ])->nullable();

            $table->timestamps();

            // performance
            $table->index('archive_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archive_cross_references');
    }
};
