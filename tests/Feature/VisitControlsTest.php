<?php

namespace Tests\Feature;

use App\Models\RouteVisit;
use App\Models\Sales;
use App\Models\SalesRoute;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Aturan check-in: tanggal rute, lompat urutan, verifikasi GPS, dan riwayat audit.
 */
class VisitControlsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $salesUser;
    private Sales $salesProfile;
    private Store $store1;
    private Store $store2;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->admin = User::factory()->admin()->create();
        $this->salesUser = User::factory()->sales()->create();
        $this->salesProfile = Sales::factory()->create(['user_id' => $this->salesUser->id]);

        // Koordinat toko tetap agar jarak GPS bisa dihitung pasti
        $this->store1 = Store::factory()->create(['latitude' => -7.5666000, 'longitude' => 110.8167000]);
        $this->store2 = Store::factory()->create(['latitude' => -7.5700000, 'longitude' => 110.8200000]);
    }

    private function makeRoute(?string $date = null): SalesRoute
    {
        return SalesRoute::factory()->create([
            'sales_id' => $this->salesProfile->id,
            'route_date' => $date ?? Carbon::now()->toDateString(),
        ]);
    }

    private function makeVisit(SalesRoute $route, Store $store, int $sequence): RouteVisit
    {
        return RouteVisit::create([
            'sales_route_id' => $route->id,
            'store_id' => $store->id,
            'sequence' => $sequence,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'result' => 'successful',
            'total_amount' => 100000,
            'photos' => [UploadedFile::fake()->image('bukti.jpg')],
        ], $overrides);
    }

    // ---------- Aturan tanggal rute ----------

    public function test_sales_cannot_checkin_on_a_different_date(): void
    {
        $route = $this->makeRoute(Carbon::now()->subDay()->toDateString());
        $visit = $this->makeVisit($route, $this->store1, 1);

        $response = $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $this->payload());

        $response->assertSessionHasErrors(['checkin']);
        $this->assertNull($visit->fresh()->checked_in_at);
    }

    public function test_admin_cannot_checkin_or_change_results(): void
    {
        $route = $this->makeRoute(Carbon::now()->toDateString());
        $visit = $this->makeVisit($route, $this->store1, 1);

        $this->actingAs($this->admin)->post(route('rute.checkin', $visit->id), $this->payload())
            ->assertForbidden();
        $this->actingAs($this->admin)->post(route('my-visits.checkin', $visit->id), $this->payload())
            ->assertForbidden();

        $visit->refresh();
        $this->assertNull($visit->checked_in_at);
        $this->assertNull($visit->result);
    }

    public function test_admin_cannot_edit_an_existing_checkin(): void
    {
        $route = $this->makeRoute(Carbon::now()->toDateString());
        $visit = $this->makeVisit($route, $this->store1, 1);
        $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $this->payload(['total_amount' => 100000]));

        $this->actingAs($this->admin)->post(route('rute.checkin', $visit->id), $this->payload(['total_amount' => 999999]))
            ->assertForbidden();

        $this->assertDatabaseHas('orders', ['route_visit_id' => $visit->id, 'total_amount' => 100000]);
    }

    // ---------- Aturan urutan ----------

    public function test_skipping_an_earlier_stop_requires_a_reason(): void
    {
        $route = $this->makeRoute();
        $this->makeVisit($route, $this->store1, 1);
        $second = $this->makeVisit($route, $this->store2, 2);

        $response = $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $second->id), $this->payload());

        $response->assertSessionHasErrors(['skip_reason']);
        $this->assertNull($second->fresh()->checked_in_at);
    }

    public function test_skipping_with_a_reason_is_allowed_and_recorded(): void
    {
        $route = $this->makeRoute();
        $this->makeVisit($route, $this->store1, 1);
        $second = $this->makeVisit($route, $this->store2, 2);

        $this->actingAs($this->salesUser)->post(
            route('my-visits.checkin', $second->id),
            $this->payload(['skip_reason' => 'Toko pertama belum buka'])
        )->assertSessionHasNoErrors();

        $second->refresh();
        $this->assertNotNull($second->checked_in_at);
        $this->assertSame('Toko pertama belum buka', $second->skip_reason);
    }

    public function test_visiting_in_order_needs_no_skip_reason(): void
    {
        $route = $this->makeRoute();
        $first = $this->makeVisit($route, $this->store1, 1);
        $second = $this->makeVisit($route, $this->store2, 2);

        $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $first->id), $this->payload())
            ->assertSessionHasNoErrors();
        $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $second->id), $this->payload())
            ->assertSessionHasNoErrors();

        $this->assertNull($second->fresh()->skip_reason);
    }

    // ---------- Verifikasi GPS ----------

    public function test_gps_near_the_store_is_marked_near(): void
    {
        $visit = $this->makeVisit($this->makeRoute(), $this->store1, 1);

        $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $this->payload([
            'simulated_latitude' => -7.5666500,
            'simulated_longitude' => 110.8167000,
            'gps_accuracy' => 20,
        ]))->assertSessionHasNoErrors();

        $visit->refresh();
        $this->assertLessThan(200, $visit->gps_distance_m);
        $this->assertSame('near', $visit->gpsStatus());
    }

    public function test_gps_far_from_the_store_is_flagged_but_not_blocked(): void
    {
        $visit = $this->makeVisit($this->makeRoute(), $this->store1, 1);

        // 0,01 derajat lintang kira-kira 1,1 km dari toko
        $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $this->payload([
            'simulated_latitude' => -7.5766000,
            'simulated_longitude' => 110.8167000,
            'gps_accuracy' => 30,
        ]))->assertSessionHasNoErrors();

        $visit->refresh();
        $this->assertNotNull($visit->checked_in_at);
        $this->assertGreaterThan(1000, $visit->gps_distance_m);
        $this->assertSame('far', $visit->gpsStatus());
    }

    public function test_checkin_without_gps_is_unverified(): void
    {
        $visit = $this->makeVisit($this->makeRoute(), $this->store1, 1);

        $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $this->payload())
            ->assertSessionHasNoErrors();

        $this->assertSame('unknown', $visit->fresh()->gpsStatus());
    }

    public function test_very_inaccurate_gps_is_unverified(): void
    {
        $visit = $this->makeVisit($this->makeRoute(), $this->store1, 1);

        $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $this->payload([
            'simulated_latitude' => -7.5766000,
            'simulated_longitude' => 110.8167000,
            'gps_accuracy' => 900,
        ]))->assertSessionHasNoErrors();

        $this->assertSame('unknown', $visit->fresh()->gpsStatus());
    }

    public function test_invalid_gps_coordinates_are_rejected(): void
    {
        $visit = $this->makeVisit($this->makeRoute(), $this->store1, 1);

        $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $this->payload([
            'simulated_latitude' => 123,
            'simulated_longitude' => 110.8,
        ]))->assertSessionHasErrors(['simulated_latitude']);
    }

    // ---------- Riwayat audit ----------

    public function test_update_keeps_original_checkin_time_and_writes_audit_log(): void
    {
        $visit = $this->makeVisit($this->makeRoute(), $this->store1, 1);

        Carbon::setTestNow(Carbon::now()->setTime(9, 0, 0));
        $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $this->payload([
            'total_amount' => 100000,
        ]))->assertSessionHasNoErrors();
        $firstTime = $visit->fresh()->checked_in_at->toDateTimeString();

        Carbon::setTestNow(Carbon::now()->setTime(11, 30, 0));
        $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), [
            'result' => 'successful',
            'total_amount' => 250000,
        ])->assertSessionHasNoErrors();

        $visit->refresh();
        $this->assertSame($firstTime, $visit->checked_in_at->toDateTimeString());

        $this->assertDatabaseHas('visit_audit_logs', ['route_visit_id' => $visit->id, 'action' => 'checkin_created']);

        $update = $visit->auditLogs()->where('action', 'checkin_updated')->first();
        $this->assertNotNull($update);
        $this->assertSame($this->salesUser->id, $update->user_id);
        $this->assertEquals(100000.0, $update->payload['changes']['total_amount']['from']);
        $this->assertEquals(250000.0, $update->payload['changes']['total_amount']['to']);

        Carbon::setTestNow();
    }

    public function test_update_without_changes_writes_no_extra_log(): void
    {
        $visit = $this->makeVisit($this->makeRoute(), $this->store1, 1);

        $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $this->payload())
            ->assertSessionHasNoErrors();
        $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), [
            'result' => 'successful',
            'total_amount' => 100000,
        ])->assertSessionHasNoErrors();

        $this->assertSame(0, $visit->auditLogs()->where('action', 'checkin_updated')->count());
    }
}
