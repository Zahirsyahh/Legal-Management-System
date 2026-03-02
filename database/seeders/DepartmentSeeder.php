<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // Cek dulu apakah sudah ada data
        if (DB::table('departments')->count() > 0) {
            $this->command->info('Departments table already has data. Skipping seeder.');
            return;
        }
        
        $departments = [
            [
                'name' => 'Legal',
                'code' => 'LEGAL',
                'description' => 'Legal Department',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Finance',
                'code' => 'FIN', 
                'description' => 'Finance Department',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Accounting',
                'code' => 'ACC',
                'description' => 'Accounting Department',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tax',
                'code' => 'TAX',
                'description' => 'Tax Department',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('departments')->insert($departments);
        $this->command->info('✓ 4 departments seeded (Simple version).');
    }
}