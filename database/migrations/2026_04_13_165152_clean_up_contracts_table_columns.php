<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'executed_file',
            'execution_notes',
            'archive_notes',
            'archiving_notes',
            'archive_file',
            'archive_location'
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('contracts', $column)) {
                Schema::table('contracts', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('executed_file')->nullable();
            $table->text('execution_notes')->nullable();
            $table->text('archive_notes')->nullable();
            $table->text('archiving_notes')->nullable();
            $table->string('archive_file')->nullable();
            $table->string('archive_location')->nullable();
        });
    }
};
