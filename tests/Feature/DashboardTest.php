<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\RouteVisit;
use App\Models\Sales;
use App\Models\SalesRoute;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    /**
     * TC-20: Dashboard Admin menampilkan 3 metrik wajib secara akurat dari basis data:
     * (1) Total Kunjungan Selesai
     * (2) Persentase Keberhasilan Kunjungan
     * (3) Total Nilai Pesanan
     */
    public function test_dashboard_displays_three_required_metrics_correctly(): void
    {
        $salesUser = User::factory()->sales()->create(['name' => 'Sales Budi']);
        $sales = Sales::factory()->create(['user_id' => $salesUser->id, 'name' => $salesUser->name]);

        $store1 = Store::factory()->create(['name' => 'Toko Berkah']);
        $store2 = Store::factory()->create(['name' => 'Warung Madura']);
        $store3 = Store::factory()->create(['name' => 'Grosir Maju']);
        $store4 = Store::factory()->create(['name' => 'Kios Lestari']);

        $route = SalesRoute::factory()->create(['sales_id' => $sales->id]);

        // 1 kunjungan Berhasil dengan Order senilai Rp 2.500.000
        $visit1 = RouteVisit::create([
            'sales_route_id' => $route->id,
            'store_id' => $store1->id,
            'sequence' => 1,
            'result' => 'successful',
            'checked_in_at' => now(),
        ]);
        Order::create([
            'route_visit_id' => $visit1->id,
            'order_number' => 'ORD-TEST-001',
            'total_amount' => 2500000.00,
        ]);

        // 1 kunjungan Berhasil dengan Order senilai Rp 1.500.000
        $visit2 = RouteVisit::create([
            'sales_route_id' => $route->id,
            'store_id' => $store2->id,
            'sequence' => 2,
            'result' => 'successful',
            'checked_in_at' => now(),
        ]);
        Order::create([
            'route_visit_id' => $visit2->id,
            'order_number' => 'ORD-TEST-002',
            'total_amount' => 1500000.00,
        ]);

        // 1 kunjungan Tutup (Rp 0)
        RouteVisit::create([
            'sales_route_id' => $route->id,
            'store_id' => $store3->id,
            'sequence' => 3,
            'result' => 'store_closed',
            'failure_reason' => 'Toko tutup',
            'checked_in_at' => now(),
        ]);

        // 1 kunjungan Ditolak (Rp 0)
        RouteVisit::create([
            'sales_route_id' => $route->id,
            'store_id' => $store4->id,
            'sequence' => 4,
            'result' => 'failed',
            'failure_reason' => 'Ditolak pemilik',
            'checked_in_at' => now(),
        ]);

        // Total Selesai: 4
        // Total Berhasil: 2
        // Persentase Keberhasilan: (2 / 4) * 100% = 50%
        // Total Nilai Pesanan: 2.500.000 + 1.500.000 = 4.000.000

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Total Kunjungan Selesai');
        $response->assertSee('4 Kunjungan');
        $response->assertSee('Tingkat Keberhasilan');
        $response->assertSee('50%');
        $response->assertSee('Total Nilai Pesanan');
        $response->assertSee('4.000.000');
    }

    /**
     * TC-21: Dashboard saat data kosong -> nilai 0 dan tidak terjadi pembagian nol (division by zero).
     */
    public function test_dashboard_with_zero_visits_handles_division_by_zero(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('0 Kunjungan');
        $response->assertSee('0%');
        $response->assertSee('Rp 0');
    }

    /**
     * TC-23: Pagination berfungsi saat data toko melebihi batas halaman.
     */
    public function test_stores_pagination_renders_when_data_exceeds_limit(): void
    {
        Store::factory()->count(15)->create();

        $response = $this->actingAs($this->admin)->get(route('stores.index'));

        $response->assertStatus(200);
        $response->assertSee('Next');
    }
}
