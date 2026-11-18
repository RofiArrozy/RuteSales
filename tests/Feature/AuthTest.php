<?php

namespace Tests\Feature;

use App\Models\Sales;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TC-01: Membuka dashboard tanpa login dialihkan ke login.
     */
    public function test_guest_accessing_dashboard_is_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    /**
     * TC-02: Sales membuka CRUD toko ditolak/diarahkan ke halaman yang diizinkan.
     */
    public function test_sales_role_accessing_stores_crud_is_redirected(): void
    {
        $salesUser = User::factory()->sales()->create();
        Sales::factory()->create(['user_id' => $salesUser->id, 'name' => $salesUser->name]);

        $response = $this->actingAs($salesUser)->get(route('stores.index'));

        // Sesuai EnsureAdmin middleware: dialihkan ke halaman Kunjungan Saya dengan pesan error
        $response->assertRedirect(route('my-visits.index'));
        $response->assertSessionHas('error');
    }

    /**
     * Halaman login menampilkan form dan demo switcher.
     */
    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Sistem');
    }

    /**
     * Quick login dan logout flow.
     */
    public function test_quick_login_and_logout_flow(): void
    {
        $admin = User::factory()->admin()->create();

        $loginResponse = $this->get(route('quick.login', $admin->id));
        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);

        $logoutResponse = $this->post(route('logout'));
        $logoutResponse->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
