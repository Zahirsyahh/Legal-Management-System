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
        Schema::table('archives', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')
                ->nullable()
                ->after('created_by');
        });
    }

    public function down()
    {
        Schema::table('archives', function (Blueprint $table) {
            $table->dropColumn('updated_by');
        });
    }
};
