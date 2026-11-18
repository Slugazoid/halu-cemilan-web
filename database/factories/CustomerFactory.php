<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Customer> */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->unique()->numerify('62813########'),
            'password' => 'test-password-123',
            'is_active' => true,
            'must_change_password' => false,
            'privacy_accepted_at' => now(),
        ];
    }
}
