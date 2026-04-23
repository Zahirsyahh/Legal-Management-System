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
        Schema::table('contract_review_stages', function (Blueprint $table) {
            $table->unsignedTinyInteger('parallel_group')
                ->nullable()
                ->default(null)
                ->comment('NULL=sequential. Nilai sama = berjalan bersamaan. Hanya diisi jika dept >= 2.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_review_stages', function (Blueprint $table) {
            $table->dropColumn('parallel_group');
        });
    }
};

