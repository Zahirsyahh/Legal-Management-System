<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Database\Seeder;

// FOR DUMMY PURPOSES
class ContractSeeder extends Seeder
{
    public function run()
    {
        // Get users by role
        $users = User::role('user')->limit(5)->get();
        $legalOfficers = User::role('legal')->limit(2)->get();
        $fatOfficers = User::role('fat')->limit(2)->get();
        
        if ($users->isEmpty() || $legalOfficers->isEmpty() || $fatOfficers->isEmpty()) {
            $this->command->warn('Please run RolePermissionSeeder first!');
            return;
        }
        
        // Create sample contracts
        foreach ($users as $user) {
            Contract::factory()->count(3)->create([
                'user_id' => $user->id,
                'legal_assigned_id' => $legalOfficers->random()->id,
                'fat_assigned_id' => $fatOfficers->random()->id,
            ]);
        }
        
        $this->command->info('Sample contracts created successfully!');
    }
}