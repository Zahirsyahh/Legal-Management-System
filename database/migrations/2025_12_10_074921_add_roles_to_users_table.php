<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('users', function (Blueprint $table) {
    if (Schema::hasColumn('users', 'roles')) {
        $table->dropColumn('roles');
    }
});
}

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('roles');
        });
    }
};