<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->timestamp('number_issued_at')->nullable()->after('final_approved_at');
        });
    }

    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('number_issued_at');
        });
    }
};