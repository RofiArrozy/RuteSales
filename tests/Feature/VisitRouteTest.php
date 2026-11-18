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

class VisitRouteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $salesUser;
    protected Sales $salesProfile;
    protected Store $store1;
    protected Store $store2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();

        $this->salesUser = User::factory()->sales()->create(['name' => 'Sales Ammar']);
        $this->salesProfile = Sales::factory()->create([
            'user_id' => $this->salesUser->id,
            'name' => $this->salesUser->name,
            'status' => 'active',
        ]);

        $this->store1 = Store::factory()->create(['name' => 'Toko Barokah 1', 'status' => 'active']);
        $this->store2 = Store::factory()->create(['name' => 'Toko Barokah 2', 'status' => 'active']);
    }

    /**
     * TC-07: Admin membuat rute dengan sales aktif -> tersimpan dengan multi-toko berurutan.
     */
    public function test_admin_creates_route_with_active_sales_and_multiple_stores(): void
    {
        $payload = [
            'name' => 'Rute Pagi Wilayah Sleman',
            'sales_id' => $this->salesProfile->id,
            'route_date' => now()->format('Y-m-d'),
            'notes' => 'Instruksi cek stok',
            'stores' => [$this->store1->id, $this->store2->id],
        ];

        $response = $this->actingAs($this->admin)->post(route('rute.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales_routes', ['name' => 'Rute Pagi Wilayah Sleman', 'sales_id' => $this->salesProfile->id]);
        $this->assertDatabaseHas('route_visits', ['store_id' => $this->store1->id, 'sequence' => 1]);
        $this->assertDatabaseHas('route_visits', ['store_id' => $this->store2->id, 'sequence' => 2]);
    }

    /**
     * TC-08: Admin menugaskan sales nonaktif -> ditolak validasi.
     */
    public function test_route_creation_fails_when_sales_is_inactive(): void
    {
        $inactiveSales = Sales::factory()->inactive()->create();

        $payload = [
            'name' => 'Rute Sales Nonaktif',
            'sales_id' => $inactiveSales->id,
            'route_date' => now()->format('Y-m-d'),
            'stores' => [$this->store1->id],
        ];

        $response = $this->actingAs($this->admin)->post(route('rute.store'), $payload);

        $response->assertSessionHasErrors(['sales_id']);
        $this->assertDatabaseMissing('sales_routes', ['name' => 'Rute Sales Nonaktif']);
    }

    /**
     * TC-09: Menambahkan toko nonaktif ke rute -> ditolak validasi.
     */
    public function test_route_creation_fails_when_store_is_inactive(): void
    {
        $inactiveStore = Store::factory()->inactive()->create(['name' => 'Toko Nonaktif']);

        $payload = [
            'name' => 'Rute Toko Tutup',
            'sales_id' => $this->salesProfile->id,
            'route_date' => now()->format('Y-m-d'),
            'stores' => [$inactiveStore->id],
        ];

        $response = $this->actingAs($this->admin)->post(route('rute.store'), $payload);

        $response->assertSessionHasErrors(['stores']);
    }

    /**
     * TC-10: Toko sama ditambahkan dua kali pada rute -> ditolak validasi keunikan.
     */
    public function test_route_creation_fails_when_duplicate_store_in_same_route(): void
    {
        $payload = [
            'name' => 'Rute Toko Duplikat',
            'sales_id' => $this->salesProfile->id,
            'route_date' => now()->format('Y-m-d'),
            'stores' => [$this->store1->id, $this->store1->id], // Duplikasi
        ];

        $response = $this->actingAs($this->admin)->post(route('rute.store'), $payload);

        $response->assertSessionHasErrors(['stores']);
    }

    /**
     * TC-11: Membuka ID rute yang valid -> detail dan urutan toko tampil.
     */
    public function test_valid_route_detail_renders_store_stops(): void
    {
        $route = SalesRoute::factory()->create(['name' => 'Rute Uji Tampil', 'sales_id' => $this->salesProfile->id]);
        RouteVisit::create(['sales_route_id' => $route->id, 'store_id' => $this->store1->id, 'sequence' => 1]);

        $response = $this->actingAs($this->admin)->get(route('rute.detail', $route->id));

        $response->assertStatus(200);
        $response->assertSee('Rute Uji Tampil');
        $response->assertSee($this->store1->name);
        $response->assertSee('#1');
    }

    /**
     * TC-12: Membuka ID rute yang tidak ada -> 404 yang sesuai.
     */
    public function test_non_existent_route_detail_returns_404(): void
    {
        $response = $this->actingAs($this->admin)->get(route('rute.detail', 999999));

        $response->assertStatus(404);
    }

    /**
     * TC-13 & TC-17: Sales mencatat hasil successful -> hasil dan waktu server tersimpan beserta order.
     */
    public function test_sales_checkin_successful_records_server_time_and_order(): void
    {
        $route = SalesRoute::factory()->create(['sales_id' => $this->salesProfile->id]);
        $visit = RouteVisit::create(['sales_route_id' => $route->id, 'store_id' => $this->store1->id, 'sequence' => 1]);

        $payload = [
            'result' => 'successful',
            'total_amount' => 1500000,
            'notes' => 'Closing 10 kardus',
        ];

        $response = $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $payload);

        $response->assertRedirect();
        $visit->refresh();
        $this->assertEquals('successful', $visit->result);
        $this->assertNotNull($visit->checked_in_at);
        $this->assertDatabaseHas('orders', [
            'route_visit_id' => $visit->id,
            'total_amount' => 1500000,
        ]);
    }

    /**
     * TC-14: Hasil failed tanpa alasan -> validasi gagal.
     */
    public function test_checkin_failed_without_reason_fails_validation(): void
    {
        $route = SalesRoute::factory()->create(['sales_id' => $this->salesProfile->id]);
        $visit = RouteVisit::create(['sales_route_id' => $route->id, 'store_id' => $this->store1->id, 'sequence' => 1]);

        $payload = [
            'result' => 'failed',
            'failure_reason' => '', // Kosong
        ];

        $response = $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $payload);

        $response->assertSessionHasErrors(['failure_reason']);
    }

    /**
     * TC-15: Hasil store_closed tanpa alasan -> validasi gagal.
     */
    public function test_checkin_store_closed_without_reason_fails_validation(): void
    {
        $route = SalesRoute::factory()->create(['sales_id' => $this->salesProfile->id]);
        $visit = RouteVisit::create(['sales_route_id' => $route->id, 'store_id' => $this->store1->id, 'sequence' => 1]);

        $payload = [
            'result' => 'store_closed',
            'failure_reason' => '',
        ];

        $response = $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $payload);

        $response->assertSessionHasErrors(['failure_reason']);
    }

    /**
     * TC-16: Sales mencoba membuka kunjungan sales lain -> akses ditolak 403.
     */
    public function test_sales_cannot_access_other_sales_visit(): void
    {
        $otherSalesUser = User::factory()->sales()->create(['name' => 'Sales Lain']);
        $otherSalesProfile = Sales::factory()->create(['user_id' => $otherSalesUser->id, 'name' => $otherSalesUser->name]);

        $otherRoute = SalesRoute::factory()->create(['sales_id' => $otherSalesProfile->id]);
        $otherVisit = RouteVisit::create(['sales_route_id' => $otherRoute->id, 'store_id' => $this->store1->id, 'sequence' => 1]);

        // Sales Ammar mencoba membuka kunjungan Sales Lain
        $response = $this->actingAs($this->salesUser)->get(route('my-visits.show', $otherVisit->id));

        $response->assertStatus(403);
    }

    /**
     * TC-18: Pesanan pada kunjungan failed -> ditolak.
     */
    public function test_order_cannot_be_created_for_failed_visit(): void
    {
        $route = SalesRoute::factory()->create(['sales_id' => $this->salesProfile->id]);
        $visit = RouteVisit::create(['sales_route_id' => $route->id, 'store_id' => $this->store1->id, 'sequence' => 1]);

        $payload = [
            'result' => 'failed',
            'failure_reason' => 'Toko menolak',
            'total_amount' => 500000, // Ditolak
        ];

        $response = $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $payload);

        $response->assertSessionHasErrors(['total_amount']);
    }

    /**
     * TC-19: Nilai total negatif -> validasi gagal.
     */
    public function test_checkin_with_negative_order_amount_fails_validation(): void
    {
        $route = SalesRoute::factory()->create(['sales_id' => $this->salesProfile->id]);
        $visit = RouteVisit::create(['sales_route_id' => $route->id, 'store_id' => $this->store1->id, 'sequence' => 1]);

        $payload = [
            'result' => 'successful',
            'total_amount' => -250000, // Negatif
        ];

        $response = $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $payload);

        $response->assertSessionHasErrors(['total_amount']);
    }
}
