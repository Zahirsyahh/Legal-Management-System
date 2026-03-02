<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Pilih salah satu OPTION di bawah ini:
        
        // ============================================
        // OPTION 1: Hanya roles & permissions (simple)
        // ============================================
        $this->call([
            RolePermissionSeeder::class,
        ]);
        
        // ============================================
        // OPTION 2: Dengan test users & sample contracts
        // ============================================
        // $this->call([
        //     RolePermissionSeeder::class,
        //     TestUsersSeeder::class,      // Uncomment jika ada file ini
        //     ContractSeeder::class,       // Uncomment jika ada file ini
        // ]);
        
        // ============================================
        // OPTION 3: Tetap ada default factory user
        // ============================================
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}