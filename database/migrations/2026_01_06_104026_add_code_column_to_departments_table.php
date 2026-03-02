// database/migrations/2026_01_07_add_code_column_to_departments_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cek jika kolom code belum ada
        if (Schema::hasTable('departments') && !Schema::hasColumn('departments', 'code')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->string('code')->unique()->nullable()->after('name');
            });
            
            // Update data existing dengan code
            DB::table('departments')->where('name', 'Legal')->update(['code' => 'LEGAL']);
            DB::table('departments')->where('name', 'Finance')->update(['code' => 'FIN']);
            DB::table('departments')->where('name', 'Accounting')->update(['code' => 'ACC']);
            DB::table('departments')->where('name', 'Tax')->update(['code' => 'TAX']);
            
            // Set code tidak boleh null
            Schema::table('departments', function (Blueprint $table) {
                $table->string('code')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('departments', 'code')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        }
    }
};