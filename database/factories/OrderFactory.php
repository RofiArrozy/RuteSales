<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\RouteVisit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'route_visit_id' => RouteVisit::factory()->successful(),
            'order_number' => 'ORD-' . fake()->unique()->numerify('##########'),
            'total_amount' => fake()->randomFloat(2, 50000, 5000000),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
