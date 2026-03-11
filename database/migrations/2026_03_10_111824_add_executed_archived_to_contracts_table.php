<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {

            $table->timestamp('executed_at')->nullable()->after('released_at');
            $table->unsignedInteger('executed_by')->nullable()->after('executed_at');

            $table->timestamp('archived_at')->nullable()->after('executed_by');
            $table->unsignedInteger('archived_by')->nullable()->after('archived_at');

            $table->foreign('executed_by')
                ->references('id_user')
                ->on('tbl_user')
                ->nullOnDelete();

            $table->foreign('archived_by')
                ->references('id_user')
                ->on('tbl_user')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {

            $table->dropForeign(['executed_by']);
            $table->dropForeign(['archived_by']);

            $table->dropColumn([
                'executed_at',
                'executed_by',
                'archived_at',
                'archived_by'
            ]);
        });
    }
};
