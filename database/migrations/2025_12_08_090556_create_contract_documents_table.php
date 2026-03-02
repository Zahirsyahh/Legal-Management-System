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
        Schema::create('contract_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('contract_id')->constrained()->onDelete('cascade');                    
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('filename');
                $table->string('filepath');
                $table->string('filetype');
                $table->bigInteger('filesize');
                $table->integer('version')->default(1);
                $table->boolean('is_final')->default(false);
                $table->text('notes')->nullable();
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_documents');
    }
};
