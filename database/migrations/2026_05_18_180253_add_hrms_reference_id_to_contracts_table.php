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
        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedInteger('hrms_reference_id')
                ->nullable()
                ->unique()
                ->after('id')
                ->comment('Referensi ID dari tbl_surat_keluar_legal HRMS');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('hrms_reference_id');
        });
    }
};
