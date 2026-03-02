<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('tbl_jabatan')) {
            return;
        }

        Schema::create('tbl_jabatan', function (Blueprint $table) {
            $table->increments('no_jabatan');
            $table->string('nama_jabatan', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_jabatan');
    }
};
