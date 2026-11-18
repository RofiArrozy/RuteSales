<?php

namespace Database\Factories;

use App\Models\RouteVisit;
use App\Models\SalesRoute;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RouteVisit>
 */
class RouteVisitFactory extends Factory
{
    protected $model = RouteVisit::class;

    public function definition(): array
    {
        return [
            'sales_route_id' => SalesRoute::factory(),
            'store_id' => Store::factory(),
            'sequence' => 1,
            'result' => null,
            'checked_in_at' => null,
            'notes' => fake()->optional()->sentence(),
            'failure_reason' => null,
        ];
    }

    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'successful',
            'checked_in_at' => now(),
        ]);
    }

    public function storeClosed(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'store_closed',
            'checked_in_at' => now(),
            'failure_reason' => 'Toko tutup saat sales berkunjung.',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'failed',
            'checked_in_at' => now(),
            'failure_reason' => 'Pemilik menolak penawaran pesanan.',
        ]);
    }
}
