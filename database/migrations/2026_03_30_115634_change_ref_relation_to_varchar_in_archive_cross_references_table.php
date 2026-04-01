<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeRefRelationToVarcharInArchiveCrossReferencesTable extends Migration
{
    public function up()
    {
        // Langkah 1: Konversi data ENUM yang sudah ada ke string biasa
        // (agar tidak error saat tipe kolom diubah)
        DB::statement("ALTER TABLE archive_cross_references MODIFY ref_relation VARCHAR(255) NULL");
    }

    public function down()
    {
        // Rollback ke ENUM semula jika diperlukan
        DB::statement("ALTER TABLE archive_cross_references MODIFY ref_relation ENUM('reference', 'amendment', 'related', 'support') NULL");
    }
}