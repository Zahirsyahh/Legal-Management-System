<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Cek dulu apakah kolom sudah ada
            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable()->after('email');
            }
            
            // is_active sudah ada, jangan tambah lagi
            // if (!Schema::hasColumn('users', 'is_active')) {
            //     $table->boolean('is_active')->default(true)->after('department');
            // }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['department']);
            // Jangan drop is_active karena sudah ada dari migration sebelumnya
        });
    }
};