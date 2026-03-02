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
    Schema::table('users', function (Blueprint $table) {
        // Pastikan kolom 'roles' ada sebelum dihapus
        if (Schema::hasColumn('users', 'roles')) {
            $table->dropColumn('roles');
        }
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('roles')->nullable()->after('updated_at');
    });
}
};
