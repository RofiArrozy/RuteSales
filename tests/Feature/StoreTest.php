<?php

namespace Tests\Feature;

use App\Models\RouteVisit;
use App\Models\SalesRoute;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    /**
     * TC-05: Admin membuat toko valid -> tersimpan dan muncul di database.
     */
    public function test_admin_can_create_valid_store(): void
    {
        $payload = [
            'name' => 'Toko Subur Makmur',
            'owner_name' => 'Pak Joko',
            'phone' => '081298765432',
            'address' => 'Jl. Pahlawan No. 45, Sleman',
            'area' => 'Sleman Tengah',
            'latitude' => -7.7123456,
            'longitude' => 110.3789012,
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->post(route('stores.store'), $payload);

        $response->assertRedirect(route('stores.index'));
        $this->assertDatabaseHas('stores', ['name' => 'Toko Subur Makmur', 'owner_name' => 'Pak Joko']);
    }

    /**
     * TC-06: Alamat toko kosong -> validasi gagal.
     */
    public function test_store_creation_fails_when_address_is_empty(): void
    {
        $payload = [
            'name' => 'Toko Tanpa Alamat',
            'owner_name' => 'Pak Rudi',
            'address' => '',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->post(route('stores.store'), $payload);

        $response->assertSessionHasErrors(['address']);
        $this->assertDatabaseMissing('stores', ['name' => 'Toko Tanpa Alamat']);
    }

    /**
     * Toko dengan riwayat kunjungan dinonaktifkan, bukan dihapus permanen.
     */
    public function test_store_with_visits_is_deactivated_instead_of_hard_deleted(): void
    {
        $store = Store::factory()->create(['name' => 'Toko Bersejarah', 'status' => 'active']);
        $route = SalesRoute::factory()->create();
        RouteVisit::create([
            'sales_route_id' => $route->id,
            'store_id' => $store->id,
            'sequence' => 1,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('stores.destroy', $store->id));

        $response->assertRedirect(route('stores.index'));
        $this->assertDatabaseHas('stores', ['id' => $store->id, 'status' => 'inactive']);
    }
}
