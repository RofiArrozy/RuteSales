<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Store>
 */
class StoreFactory extends Factory
{
    protected $model = Store::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $prefixes = ['Toko Kelontong', 'Warung Madura', 'Minimarket', 'Grosir Sembako', 'Kios Berkah'];
        $storeName = fake()->randomElement($prefixes) . ' ' . fake()->firstName();

        return [
            'name' => $storeName,
            'owner_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'street' => null,
            'village_code' => null,
            'area' => fake()->randomElement(['Jakarta Selatan', 'Jakarta Timur', 'Jakarta Barat', 'Jakarta Pusat', 'Depok', 'Bekasi', 'Tangerang']),
            'latitude' => fake()->latitude(-6.35, -6.15),
            'longitude' => fake()->longitude(106.75, 106.95),
            'status' => 'active',
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * State toko dengan alamat terstruktur (Makamhaji, Kartasura, Sukoharjo) yang konsisten dengan data wilayah.
     */
    public function structured(): static
    {
        return $this->state(function () {
            $region = \App\Support\Wilayah::resolve('33', '33.11', '33.11.12', '33.11.12.2006');

            return [
                'street' => 'Jl. Contoh No. 1',
                'province_code' => '33',
                'regency_code' => '33.11',
                'district_code' => '33.11.12',
                'village_code' => '33.11.12.2006',
                'postal_code' => $region['village']['postal_code'],
                'address' => \App\Support\Wilayah::compose('Jl. Contoh No. 1', $region),
                'area' => $region['district']['name'],
                'latitude' => $region['village']['lat'],
                'longitude' => $region['village']['lng'],
            ];
        });
    }

    /**
     * State untuk toko non-aktif.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
