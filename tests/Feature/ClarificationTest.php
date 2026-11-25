<?php

namespace Tests\Feature;

use App\Models\RouteVisit;
use App\Models\Sales;
use App\Models\SalesRoute;
use App\Models\Store;
use App\Models\User;
use App\Models\VisitAuditLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClarificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $salesUser;
    private Sales $sales;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->admin = User::factory()->admin()->create();
        $this->salesUser = User::factory()->sales()->create();
        $this->sales = Sales::factory()->create(['user_id' => $this->salesUser->id, 'name' => $this->salesUser->name]);
    }

    private function checkedInVisit(?string $date = null): RouteVisit
    {
        $route = SalesRoute::factory()->create([
            'sales_id' => $this->sales->id,
            'route_date' => $date ?? Carbon::now()->toDateString(),
        ]);
        $visit = RouteVisit::create([
            'sales_route_id' => $route->id,
            'store_id' => Store::factory()->create()->id,
            'sequence' => 1,
        ]);
        // check-in dilakukan sales pada hari rute
        $visit->update(['result' => 'successful', 'checked_in_at' => now()->subDay()]);
        \App\Models\Order::create(['route_visit_id' => $visit->id, 'order_number' => 'ORD-X-' . $visit->id, 'total_amount' => 100000]);
        \App\Models\VisitPhoto::create(['route_visit_id' => $visit->id, 'path' => 'visit-photos/' . $visit->id . '/a.jpg']);

        return $visit;
    }

    private function answerPayload(array $over = []): array
    {
        return array_merge([
            'result' => 'successful',
            'total_amount' => 100000,
            'clarification_response' => 'Lokasi GPS meleset, saya memang di depan toko.',
        ], $over);
    }

    public function test_admin_can_request_clarification_without_changing_the_result(): void
    {
        $visit = $this->checkedInVisit();

        $this->actingAs($this->admin)->post(route('rute.clarify', $visit->id), ['clarification_note' => 'Mohon jelaskan nominal pesanan.'])
            ->assertRedirect(route('rute.detail', $visit->sales_route_id))
            ->assertSessionHas('success');

        $visit->refresh();
        $this->assertTrue($visit->hasOpenClarification());
        $this->assertSame('Mohon jelaskan nominal pesanan.', $visit->clarification_note);
        $this->assertSame($this->admin->id, $visit->clarification_requested_by);
        $this->assertSame('successful', $visit->result);
        $this->assertDatabaseHas('orders', ['route_visit_id' => $visit->id, 'total_amount' => 100000]);
        $this->assertDatabaseHas('visit_audit_logs', ['route_visit_id' => $visit->id, 'action' => 'clarification_requested', 'user_id' => $this->admin->id]);
    }

    public function test_clarification_requires_a_note(): void
    {
        $visit = $this->checkedInVisit();

        $this->actingAs($this->admin)->post(route('rute.clarify', $visit->id), ['clarification_note' => ''])
            ->assertSessionHasErrors('clarification_note');
        $this->assertFalse($visit->fresh()->hasOpenClarification());
    }

    public function test_clarification_cannot_be_requested_before_checkin(): void
    {
        $route = SalesRoute::factory()->create(['sales_id' => $this->sales->id]);
        $visit = RouteVisit::create(['sales_route_id' => $route->id, 'store_id' => Store::factory()->create()->id, 'sequence' => 1]);

        $this->actingAs($this->admin)->post(route('rute.clarify', $visit->id), ['clarification_note' => 'Tes'])
            ->assertSessionHas('error');
        $this->assertNull($visit->fresh()->clarification_requested_at);
    }

    public function test_second_request_is_rejected_while_one_is_open(): void
    {
        $visit = $this->checkedInVisit();
        $this->actingAs($this->admin)->post(route('rute.clarify', $visit->id), ['clarification_note' => 'Pertama']);

        $this->actingAs($this->admin)->post(route('rute.clarify', $visit->id), ['clarification_note' => 'Kedua'])
            ->assertSessionHas('error');

        $this->assertSame('Pertama', $visit->fresh()->clarification_note);
    }

    public function test_sales_cannot_request_clarification(): void
    {
        $visit = $this->checkedInVisit();

        $this->actingAs($this->salesUser)->post(route('rute.clarify', $visit->id), ['clarification_note' => 'Saya sales'])
            ->assertRedirect(route('my-visits.index'));
        $this->assertNull($visit->fresh()->clarification_requested_at);
    }

    public function test_sales_must_answer_when_updating_a_visit_with_open_clarification(): void
    {
        $visit = $this->checkedInVisit();
        $this->actingAs($this->admin)->post(route('rute.clarify', $visit->id), ['clarification_note' => 'Jelaskan']);

        $payload = $this->answerPayload(['photos' => [UploadedFile::fake()->image('b.jpg')]]);
        unset($payload['clarification_response']);

        $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $payload)
            ->assertSessionHasErrors('clarification_response');
        $this->assertTrue($visit->fresh()->hasOpenClarification());
    }

    public function test_sales_answer_closes_the_clarification_and_is_audited(): void
    {
        $visit = $this->checkedInVisit();
        $firstCheckin = $visit->fresh()->checked_in_at->toDateTimeString();
        $this->actingAs($this->admin)->post(route('rute.clarify', $visit->id), ['clarification_note' => 'Jelaskan']);

        $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $this->answerPayload())
            ->assertSessionHasNoErrors();

        $visit->refresh();
        $this->assertFalse($visit->hasOpenClarification());
        $this->assertNotNull($visit->clarification_answered_at);
        $this->assertSame('Lokasi GPS meleset, saya memang di depan toko.', $visit->clarification_response);
        $this->assertSame($firstCheckin, $visit->checked_in_at->toDateTimeString());
        $this->assertDatabaseHas('visit_audit_logs', ['route_visit_id' => $visit->id, 'action' => 'clarification_answered', 'user_id' => $this->salesUser->id]);
    }

    public function test_sales_can_answer_on_a_different_date_but_cannot_edit_without_a_request(): void
    {
        $visit = $this->checkedInVisit(Carbon::now()->subDays(2)->toDateString());

        // tanpa klarifikasi: ditolak karena bukan tanggal rute
        $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $this->answerPayload())
            ->assertSessionHasErrors('checkin');

        // dengan klarifikasi terbuka: boleh menjawab di hari lain
        $this->actingAs($this->admin)->post(route('rute.clarify', $visit->id), ['clarification_note' => 'Jelaskan']);
        $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $this->answerPayload())
            ->assertSessionHasNoErrors();
        $this->assertFalse($visit->fresh()->hasOpenClarification());
    }

    public function test_open_and_answered_clarifications_are_flagged_for_review(): void
    {
        $visit = $this->checkedInVisit();
        $this->assertSame(0, RouteVisit::flagged()->count());

        $this->actingAs($this->admin)->post(route('rute.clarify', $visit->id), ['clarification_note' => 'Jelaskan']);
        $this->assertSame(1, RouteVisit::flagged()->count());
        $this->assertSame(1, RouteVisit::openClarification()->count());

        $this->actingAs($this->salesUser)->post(route('my-visits.checkin', $visit->id), $this->answerPayload());
        $this->assertSame(1, RouteVisit::flagged()->count());
        $this->assertSame(0, RouteVisit::openClarification()->count());
    }

    public function test_admin_route_detail_has_no_checkin_form_but_offers_clarification(): void
    {
        $visit = $this->checkedInVisit();

        $this->actingAs($this->admin)->get(route('rute.detail', $visit->sales_route_id))
            ->assertOk()
            ->assertSee('Minta Klarifikasi')
            ->assertDontSee('Simpan Check-in')
            ->assertDontSee(route('rute.checkin', $visit->id), false);
    }

    public function test_admin_sees_read_only_page_on_visit_detail(): void
    {
        $visit = $this->checkedInVisit();

        $this->actingAs($this->admin)->get(route('my-visits.show', $visit->id))
            ->assertOk()
            ->assertSee('Admin hanya dapat melihat hasil check-in')
            ->assertDontSee('Simpan Hasil Check-in');
    }

    public function test_sales_sees_clarification_banner_and_answer_field(): void
    {
        $visit = $this->checkedInVisit();
        $this->actingAs($this->admin)->post(route('rute.clarify', $visit->id), ['clarification_note' => 'Mohon jelaskan jarak GPS']);

        $this->actingAs($this->salesUser)->get(route('my-visits.show', $visit->id))
            ->assertOk()
            ->assertSee('Admin meminta klarifikasi')
            ->assertSee('Mohon jelaskan jarak GPS')
            ->assertSee('name="clarification_response"', false);

        $this->actingAs($this->salesUser)->get(route('my-visits.index'))
            ->assertOk()
            ->assertSee('Perlu klarifikasi');
    }

    public function test_dashboard_counts_clarification_in_review_total(): void
    {
        $visit = $this->checkedInVisit(Carbon::now()->toDateString());
        $this->actingAs($this->admin)->post(route('rute.clarify', $visit->id), ['clarification_note' => 'Jelaskan']);

        $res = $this->actingAs($this->admin)->get(route('dashboard'));
        $this->assertSame(1, $res->viewData('flaggedCount'));
    }
}
