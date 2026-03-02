<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Ambil definisi ENUM saat ini
        $column = DB::select("SHOW COLUMNS FROM contracts WHERE Field = 'status'")[0];
        $enumValues = [];
        
        // Parse ENUM values dari string
        preg_match("/^enum\(\'(.*)\'\)$/", $column->Type, $matches);
        if (isset($matches[1])) {
            $enumValues = explode("','", $matches[1]);
        }
        
        // Tambahkan 'number_issued' jika belum ada
        if (!in_array('number_issued', $enumValues)) {
            $enumValues[] = 'number_issued';
            
            // Buat query untuk mengubah ENUM
            $newEnum = "enum('" . implode("','", $enumValues) . "')";
            
            DB::statement("ALTER TABLE contracts MODIFY status " . $newEnum);
        }
    }

    public function down()
    {
        // Kembalikan ke ENUM tanpa 'number_issued'
        $column = DB::select("SHOW COLUMNS FROM contracts WHERE Field = 'status'")[0];
        $enumValues = [];
        
        preg_match("/^enum\(\'(.*)\'\)$/", $column->Type, $matches);
        if (isset($matches[1])) {
            $enumValues = explode("','", $matches[1]);
        }
        
        // Hapus 'number_issued' dari array
        $enumValues = array_filter($enumValues, function($value) {
            return $value !== 'number_issued';
        });
        
        $newEnum = "enum('" . implode("','", $enumValues) . "')";
        
        DB::statement("ALTER TABLE contracts MODIFY status " . $newEnum);
    }
};