<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            if (!Schema::hasColumn('contracts', 'drafting_deadline')) {
                $table->date('drafting_deadline')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            if (Schema::hasColumn('contracts', 'drafting_deadline')) {
                $table->dropColumn('drafting_deadline');
            }
        });
    }
};
