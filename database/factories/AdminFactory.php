<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Admin> */
class AdminFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->numerify('62812########'),
            'password' => 'test-password-123',
            'role' => 'staff',
            'is_active' => true,
            'must_change_password' => false,
        ];
    }

    public function owner(): static
    {
        return $this->state(['role' => 'owner', 'owner_slot' => true]);
    }
}
