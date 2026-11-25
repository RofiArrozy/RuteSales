<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\RouteVisit;
use App\Models\Sales;
use App\Models\SalesRoute;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuditHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function userPayload(User $u, array $over = []): array
    {
        $payload = array_merge([
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role,
            'phone' => '08123456789',
            'employee_code' => $u->employee_code,
            'is_active' => '1',
        ], $over);

        // Checkbox yang tidak dicentang tidak dikirim browser sama sekali (bukan null)
        return array_filter($payload, fn ($v) => $v !== null);
    }

    public function test_inactive_user_cannot_login_and_sees_specific_message(): void
    {
        $u = User::factory()->sales()->create(['is_active' => false]);

        $res = $this->post(route('login.submit'), ['email' => $u->email, 'password' => 'password']);

        $res->assertSessionHasErrors('email');
        $this->assertStringContainsString('dinonaktifkan', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_active_user_can_still_login(): void
    {
        $u = User::factory()->admin()->create();

        $this->post(route('login.submit'), ['email' => $u->email, 'password' => 'password'])
            ->assertRedirect();
        $this->assertAuthenticatedAs($u);
    }

    public function test_user_deactivated_mid_session_is_logged_out(): void
    {
        $u = User::factory()->admin()->create();
        $this->actingAs($u);
        $u->update(['is_active' => false]);

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_login_is_throttled_after_five_failures(): void
    {
        RateLimiter::clear('x@example.com|127.0.0.1');
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.submit'), ['email' => 'x@example.com', 'password' => 'salah']);
        }

        $res = $this->post(route('login.submit'), ['email' => 'x@example.com', 'password' => 'salah']);
        $res->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('email'));
    }

    public function test_admin_cannot_deactivate_or_demote_self(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->admin()->create(); // admin lain agar yang diuji murni aturan "diri sendiri"

        $this->actingAs($admin)->put(route('sales.update', $admin->id), $this->userPayload($admin, ['is_active' => null]))
            ->assertSessionHas('error');
        $this->actingAs($admin)->put(route('sales.update', $admin->id), $this->userPayload($admin, ['role' => 'sales']))
            ->assertSessionHas('error');

        $admin->refresh();
        $this->assertTrue($admin->is_active);
        $this->assertSame('admin', $admin->role);
    }

    public function test_route_with_checkin_is_cancelled_not_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $route = SalesRoute::factory()->create(['status' => 'in_progress']);
        RouteVisit::factory()->successful()->create([
            'sales_route_id' => $route->id,
            'checked_in_at' => now(),
        ]);

        $this->actingAs($admin)->delete(route('rute.destroy', $route->id))->assertRedirect(route('rute.index'));

        $this->assertDatabaseHas('sales_routes', ['id' => $route->id, 'status' => 'cancelled']);
        $this->assertDatabaseCount('route_visits', 1);
    }

    public function test_route_without_checkin_is_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $route = SalesRoute::factory()->create();
        RouteVisit::factory()->create(['sales_route_id' => $route->id]);

        $this->actingAs($admin)->delete(route('rute.destroy', $route->id));

        $this->assertDatabaseMissing('sales_routes', ['id' => $route->id]);
    }

    private function salesVisit(): array
    {
        $user = User::factory()->sales()->create();
        $sales = Sales::factory()->create(['user_id' => $user->id, 'name' => $user->name]);
        $route = SalesRoute::factory()->create(['sales_id' => $sales->id, 'route_date' => now()->toDateString()]);
        $visit = RouteVisit::factory()->create(['sales_route_id' => $route->id, 'store_id' => Store::factory()->create()->id, 'sequence' => 1]);

        return [$user, $visit];
    }

    private function checkinPayload(array $over = []): array
    {
        return array_merge([
            'result' => 'successful',
            'total_amount' => 100000,
            'photos' => [\Illuminate\Http\UploadedFile::fake()->image('bukti.jpg')],
        ], $over);
    }

    public function test_duplicate_order_number_is_rejected_with_validation_error(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        [$user, $visit] = $this->salesVisit();
        $other = Order::factory()->create(['order_number' => 'ORD-DUP']);

        $this->actingAs($user)->post(route('my-visits.checkin', $visit->id), $this->checkinPayload(['order_number' => 'ORD-DUP']))
            ->assertSessionHasErrors('order_number');
    }

    public function test_oversized_order_amount_is_rejected(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        [$user, $visit] = $this->salesVisit();

        $this->actingAs($user)->post(route('my-visits.checkin', $visit->id), $this->checkinPayload(['total_amount' => 99999999999999999]))
            ->assertSessionHasErrors('total_amount');
    }

    public function test_editing_checkin_keeps_existing_order_number(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        [$user, $visit] = $this->salesVisit();

        $this->actingAs($user)->post(route('my-visits.checkin', $visit->id), $this->checkinPayload(['order_number' => 'ORD-KEEP']));
        $this->actingAs($user)->post(route('my-visits.checkin', $visit->id), $this->checkinPayload(['total_amount' => 250000]));

        $this->assertDatabaseHas('orders', ['route_visit_id' => $visit->id, 'order_number' => 'ORD-KEEP', 'total_amount' => 250000]);
    }
}
