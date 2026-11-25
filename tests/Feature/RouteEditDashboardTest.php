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

class RouteEditDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Sales $sales;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $su = User::factory()->sales()->create();
        $this->sales = Sales::factory()->create(['user_id' => $su->id, 'name' => $su->name]);
    }

    private function routeWithStores(int $n = 2, array $routeAttrs = []): array
    {
        $route = SalesRoute::factory()->create(['sales_id' => $this->sales->id, 'route_date' => now()->toDateString()] + $routeAttrs);
        $stores = Store::factory()->count($n)->create();
        foreach ($stores as $i => $s) {
            RouteVisit::factory()->create(['sales_route_id' => $route->id, 'store_id' => $s->id, 'sequence' => $i + 1]);
        }

        return [$route, $stores];
    }

    private function payload(SalesRoute $route, array $over = []): array
    {
        return array_merge([
            'name' => $route->name,
            'route_date' => $route->route_date->format('Y-m-d'),
            'status' => 'planned',
        ], $over);
    }

    public function test_edit_page_renders_for_route(): void
    {
        [$route] = $this->routeWithStores();

        $this->actingAs($this->admin)->get(route('rute.edit', $route->id))
            ->assertOk()->assertSee('Urutan Toko');
    }

    public function test_admin_can_reorder_and_change_stores_before_any_checkin(): void
    {
        [$route, $stores] = $this->routeWithStores(2);
        $third = Store::factory()->create();

        $this->actingAs($this->admin)->put(route('rute.update', $route->id), $this->payload($route, [
            'stores' => [$stores[1]->id, $third->id, $stores[0]->id],
        ]))->assertRedirect(route('rute.detail', $route->id));

        $ordered = $route->visits()->orderBy('sequence')->pluck('store_id')->all();
        $this->assertSame([$stores[1]->id, $third->id, $stores[0]->id], $ordered);
    }

    public function test_stores_cannot_be_changed_after_a_checkin(): void
    {
        [$route, $stores] = $this->routeWithStores(2);
        $route->visits()->first()->update(['result' => 'successful', 'checked_in_at' => now()]);

        $this->actingAs($this->admin)->put(route('rute.update', $route->id), $this->payload($route, [
            'stores' => [$stores[1]->id],
        ]))->assertSessionHasErrors('stores');

        $this->assertSame(2, $route->visits()->count());
    }

    public function test_duplicate_stores_are_rejected_on_update(): void
    {
        [$route, $stores] = $this->routeWithStores(2);

        $this->actingAs($this->admin)->put(route('rute.update', $route->id), $this->payload($route, [
            'stores' => [$stores[0]->id, $stores[0]->id],
        ]))->assertSessionHasErrors('stores');
    }

    public function test_route_cannot_be_completed_while_stops_are_unvisited(): void
    {
        [$route] = $this->routeWithStores(2);

        $this->actingAs($this->admin)->put(route('rute.update', $route->id), $this->payload($route, ['status' => 'completed']))
            ->assertSessionHasErrors('status');
        $this->assertSame('planned', $route->fresh()->status);
    }

    public function test_route_can_be_completed_when_all_stops_have_results(): void
    {
        [$route] = $this->routeWithStores(2);
        $route->visits()->update(['result' => 'store_closed', 'failure_reason' => 'Tutup', 'checked_in_at' => now()]);

        $this->actingAs($this->admin)->put(route('rute.update', $route->id), $this->payload($route, ['status' => 'completed']))
            ->assertSessionHasNoErrors();
        $this->assertSame('completed', $route->fresh()->status);
    }

    public function test_changing_date_to_the_past_is_rejected_but_unchanged_past_date_is_allowed(): void
    {
        [$route] = $this->routeWithStores(1, ['route_date' => now()->subDays(3)->toDateString()]);

        // Tanggal lama (sudah lewat) tidak diubah -> boleh
        $this->actingAs($this->admin)->put(route('rute.update', $route->id), $this->payload($route, ['name' => 'Nama baru']))
            ->assertSessionHasNoErrors();

        // Mengubah ke tanggal lampau lain -> ditolak
        $this->actingAs($this->admin)->put(route('rute.update', $route->id), $this->payload($route, [
            'route_date' => now()->subDays(1)->toDateString(),
        ]))->assertSessionHasErrors('route_date');
    }

    public function test_creating_route_in_the_past_is_rejected(): void
    {
        $store = Store::factory()->create();

        $this->actingAs($this->admin)->post(route('rute.store'), [
            'sales_id' => $this->sales->id,
            'route_date' => now()->subDay()->toDateString(),
            'name' => 'Rute Lampau',
            'stores' => [$store->id],
        ])->assertSessionHasErrors('route_date');
    }

    public function test_dashboard_shows_flagged_count_and_aggregated_sales_performance(): void
    {
        [$route, $stores] = $this->routeWithStores(3);
        [$v1, $v2, $v3] = $route->visits()->orderBy('sequence')->get()->all();

        $v1->update(['result' => 'successful', 'checked_in_at' => now()]);
        Order::create(['route_visit_id' => $v1->id, 'order_number' => 'ORD-A', 'total_amount' => 750000]);
        $v2->update(['result' => 'failed', 'failure_reason' => 'Ditolak', 'checked_in_at' => now(), 'skip_reason' => 'Toko 1 tutup']);
        // $v3 belum dikunjungi

        $res = $this->actingAs($this->admin)->get(route('dashboard'));

        $res->assertOk()->assertSee('Perlu Ditinjau');
        $this->assertSame(1, $res->viewData('flaggedCount'));

        $perf = $res->viewData('salesPerformance')->firstWhere('id', $this->sales->id);
        $this->assertSame(3, $perf->total_visits);
        $this->assertSame(2, $perf->completed_visits);
        $this->assertSame(1, $perf->success_visits);
        $this->assertEquals(750000.0, $perf->total_revenue);
    }
}
