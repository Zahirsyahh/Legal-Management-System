<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations
     */
    public function up(): void
    {
        Schema::table('archives', function (Blueprint $table) {

            $table->string('doc_type', 10)->after('doc_name');

        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('archives', function (Blueprint $table) {

            $table->dropColumn('doc_type');

        });
    }
};
