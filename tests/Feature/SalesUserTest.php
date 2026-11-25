<?php

namespace Tests\Feature;

use App\Models\Sales;
use App\Models\SalesRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    private function makeSalesWithRoutes(int $routes): void
    {
        $user = User::factory()->sales()->create();
        $profile = Sales::factory()->create(['user_id' => $user->id, 'name' => $user->name]);
        SalesRoute::factory()->count($routes)->create(['sales_id' => $profile->id]);
    }

    private function countQueriesOnIndex(): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($this->admin)->get(route('sales.index'))->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    /**
     * Daftar sales menampilkan jumlah rute yang benar dan tidak memicu N+1 query.
     */
    public function test_sales_index_shows_route_count_without_n_plus_one(): void
    {
        $this->makeSalesWithRoutes(3);
        $this->makeSalesWithRoutes(1);

        $this->actingAs($this->admin)->get(route('sales.index'))
            ->assertOk()
            ->assertSee('3 rute aktif')
            ->assertSee('1 rute aktif');

        $queriesWithFewUsers = $this->countQueriesOnIndex();

        foreach (range(1, 5) as $i) {
            $this->makeSalesWithRoutes(2);
        }

        $queriesWithManyUsers = $this->countQueriesOnIndex();

        // Jumlah query harus tetap meski jumlah baris bertambah
        $this->assertSame($queriesWithFewUsers, $queriesWithManyUsers);
    }
}
