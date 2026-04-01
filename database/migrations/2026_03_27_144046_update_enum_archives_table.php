<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    // =========================
    // UPDATE DATA LAMA DULU
    // =========================

    DB::table('archives')
        ->where('doc_status', 'scancopy')
        ->update(['doc_status' => 'scan']);

    DB::table('archives')
        ->where('doc_status', 'hardcopy')
        ->update(['doc_status' => 'original hardcopy']);

    DB::table('archives')
        ->where('doc_status', 'born_digital')
        ->update(['doc_status' => 'copy']); // atau 'copy'

    DB::table('archives')
        ->where('version_status', 'active')
        ->update(['version_status' => 'latest']);

    DB::table('archives')
        ->where('version_status', 'terminate')
        ->update(['version_status' => 'obsolete']);

    // =========================
    // UBAH ENUM
    // =========================

    DB::statement("
        ALTER TABLE archives 
        MODIFY doc_status ENUM('scan','copy','original hardcopy')
    ");

    DB::statement("
        ALTER TABLE archives 
        MODIFY version_status ENUM('latest','obsolete','superseded')
    ");
}

};
