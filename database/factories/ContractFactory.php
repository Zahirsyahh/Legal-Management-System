<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

// FOR DUMMY PURPOSES
class ContractFactory extends Factory
{
    protected $model = Contract::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(3),
            'purpose' => $this->faker->paragraph(2),
            'status' => $this->faker->randomElement(array_keys(Contract::getStatuses())),
            'user_id' => User::factory(),
            'legal_assigned_id' => User::factory(),
            'fat_assigned_id' => User::factory(),
        ];
    }
}