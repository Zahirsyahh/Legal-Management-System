<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            
            // FILE SURAT (UPLOAD INTERNAL)
            $table->string('surat_file_path')->nullable()->after('synology_folder_path');
            $table->unsignedBigInteger('surat_file_size')->nullable()->after('surat_file_path');
            $table->string('surat_file_mime', 100)->nullable()->after('surat_file_size');

        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            
            $table->dropColumn([
                'surat_file_path',
                'surat_file_size',
                'surat_file_mime',
            ]);

        });
    }
};
