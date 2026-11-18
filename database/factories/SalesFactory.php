<?php

namespace Database\Factories;

use App\Models\Sales;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sales>
 */
class SalesFactory extends Factory
{
    protected $model = Sales::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->sales(),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'employee_code' => 'SLS-' . fake()->unique()->numberBetween(1000, 9999),
            'status' => 'active',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
