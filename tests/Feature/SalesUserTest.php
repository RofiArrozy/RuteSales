<?php

namespace Tests\Feature;

use App\Models\Sales;
use App\Models\SalesRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesUserTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    /**
     * TC-03: Admin membuat sales valid, tersimpan dan profil sales otomatis terbuat.
     */
    public function test_admin_can_create_valid_sales_user(): void
    {
        $payload = [
            'name' => 'Doni Darmawan',
            'email' => 'doni@rutesales.com',
            'password' => 'secret123',
            'role' => 'sales',
            'phone' => '081234567890',
            'employee_code' => 'SLS-999',
            'is_active' => '1',
        ];

        $response = $this->actingAs($this->admin)->post(route('sales.store'), $payload);

        $response->assertRedirect(route('sales.index'));
        $this->assertDatabaseHas('users', ['email' => 'doni@rutesales.com', 'name' => 'Doni Darmawan']);
        $this->assertDatabaseHas('sales', ['name' => 'Doni Darmawan', 'employee_code' => 'SLS-999', 'status' => 'active']);
    }

    /**
     * TC-04: Nama sales kosong -> validasi gagal, tidak tersimpan.
     */
    public function test_sales_creation_fails_when_name_is_empty(): void
    {
        $payload = [
            'name' => '',
            'email' => 'sales_noname@rutesales.com',
            'password' => 'secret123',
            'role' => 'sales',
        ];

        $response = $this->actingAs($this->admin)->post(route('sales.store'), $payload);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseMissing('users', ['email' => 'sales_noname@rutesales.com']);
    }

    /**
     * Deaktivasi sales jika memiliki riwayat rute.
     */
    public function test_sales_with_routes_is_deactivated_instead_of_hard_deleted(): void
    {
        $salesUser = User::factory()->sales()->create(['name' => 'Sales Berpengalaman']);
        $salesProfile = Sales::factory()->create(['user_id' => $salesUser->id, 'name' => $salesUser->name]);
        SalesRoute::factory()->create(['sales_id' => $salesProfile->id]);

        $response = $this->actingAs($this->admin)->delete(route('sales.destroy', $salesUser->id));

        $response->assertRedirect(route('sales.index'));
        $this->assertDatabaseHas('users', ['id' => $salesUser->id, 'is_active' => false]);
        $this->assertDatabaseHas('sales', ['id' => $salesProfile->id, 'status' => 'inactive']);
    }
}
