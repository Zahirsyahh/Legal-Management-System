<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_contract_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->unsignedInteger('legal_user_id');
            $table->text('notes');
            $table->timestamps();

            $table->foreign('contract_id')
                ->references('id')
                ->on('contracts')
                ->cascadeOnDelete();

            $table->foreign('legal_user_id')
                ->references('id_user')
                ->on('tbl_user')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_contract_comments');
    }
};