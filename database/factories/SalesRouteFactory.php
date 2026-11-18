<?php

namespace Database\Factories;

use App\Models\Sales;
use App\Models\SalesRoute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalesRoute>
 */
class SalesRouteFactory extends Factory
{
    protected $model = SalesRoute::class;

    public function definition(): array
    {
        return [
            'sales_id' => Sales::factory(),
            'route_date' => fake()->date(),
            'name' => 'Rute ' . fake()->streetName(),
            'status' => 'planned',
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
}
