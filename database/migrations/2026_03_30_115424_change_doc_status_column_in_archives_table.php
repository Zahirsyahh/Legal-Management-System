<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Konversi data lama yang masih string (misal: "scan")
        // menjadi JSON array (misal: '["scan"]') sebelum ubah tipe kolom
        DB::statement('UPDATE archives SET doc_status = JSON_ARRAY(doc_status) WHERE doc_status IS NOT NULL AND doc_status NOT LIKE "[%"');

        // Ubah tipe kolom ke JSON
        DB::statement('ALTER TABLE archives MODIFY doc_status JSON NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback ke VARCHAR jika diperlukan
        DB::statement('ALTER TABLE archives MODIFY doc_status VARCHAR(255) NULL');
    }
};