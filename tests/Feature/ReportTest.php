<?php

namespace Tests\Feature;

use App\Models\RouteVisit;
use App\Models\Sales;
use App\Models\SalesRoute;
use App\Models\Store;
use App\Models\User;
use App\Models\VisitAuditLog;
use App\Support\PhotoThumb;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Sales $sales;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->admin = User::factory()->admin()->create();
        $salesUser = User::factory()->sales()->create();
        $this->sales = Sales::factory()->create(['user_id' => $salesUser->id]);
    }

    private function visit(string $storeName, array $attrs = []): RouteVisit
    {
        $route = SalesRoute::factory()->create([
            'sales_id' => $this->sales->id,
            'route_date' => Carbon::now()->toDateString(),
        ]);
        $store = Store::factory()->create(['name' => $storeName]);

        return RouteVisit::create(array_merge([
            'sales_route_id' => $route->id,
            'store_id' => $store->id,
            'sequence' => 1,
            'result' => 'successful',
            'checked_in_at' => Carbon::now(),
        ], $attrs));
    }

    private function reportParams(array $extra = []): array
    {
        return array_merge([
            'start_date' => Carbon::now()->toDateString(),
            'end_date' => Carbon::now()->toDateString(),
        ], $extra);
    }

    public function test_non_admin_cannot_open_reports(): void
    {
        $salesUser = User::factory()->sales()->create();

        $this->actingAs($salesUser)->get(route('reports.index'))->assertRedirect(route('my-visits.index'));
    }

    public function test_report_shows_flags_for_far_skipped_and_updated_visits(): void
    {
        $far = $this->visit('Toko Jauh Sekali', ['gps_distance_m' => 1500, 'gps_accuracy_m' => 20]);
        $skip = $this->visit('Toko Lompat', ['skip_reason' => 'Toko pertama tutup', 'gps_distance_m' => 10, 'gps_accuracy_m' => 5]);
        $upd = $this->visit('Toko Diperbarui', ['gps_distance_m' => 10, 'gps_accuracy_m' => 5]);
        VisitAuditLog::create(['route_visit_id' => $upd->id, 'user_id' => $this->admin->id, 'action' => 'checkin_updated', 'payload' => ['changes' => []]]);

        $response = $this->actingAs($this->admin)->get(route('reports.index', $this->reportParams()));

        $response->assertOk();
        $response->assertSee('Lokasi jauh 1.500 m');
        $response->assertSee('Lompat urutan');
        $response->assertSee('Diperbarui 1x');
    }

    public function test_flagged_filter_hides_clean_visits(): void
    {
        $this->visit('Toko Bersih Aman', ['gps_distance_m' => 20, 'gps_accuracy_m' => 5]);
        $this->visit('Toko Bermasalah Jauh', ['gps_distance_m' => 2000, 'gps_accuracy_m' => 10]);

        $response = $this->actingAs($this->admin)->get(route('reports.index', $this->reportParams(['flagged' => 1])));

        $response->assertOk();
        // Nama toko juga muncul di dropdown filter, jadi periksa baris tabel (data view), bukan teks halaman.
        $names = $response->viewData('visits')->map(fn ($v) => $v->store->name)->all();
        $this->assertSame(['Toko Bermasalah Jauh'], $names);
    }

    /**
     * Scope SQL far() harus selaras dengan logika PHP gpsStatus().
     */
    public function test_far_scope_matches_gps_status_logic(): void
    {
        $cases = [
            [1500, 20], [250, 100], [250, 0], [200, 0], [201, 0], [10, 5], [5000, 900], [600, 500], [null, null], [900, null],
        ];

        foreach ($cases as $i => [$distance, $accuracy]) {
            $this->visit("Toko Kasus {$i}", ['gps_distance_m' => $distance, 'gps_accuracy_m' => $accuracy]);
        }

        $expected = RouteVisit::all()->filter(fn ($v) => $v->gpsStatus() === 'far')->pluck('id')->sort()->values()->all();
        $actual = RouteVisit::far()->pluck('id')->sort()->values()->all();

        $this->assertSame($expected, $actual);
    }

    public function test_pdf_export_or_clear_message_when_package_missing(): void
    {
        $this->visit('Toko PDF', ['gps_distance_m' => 20, 'gps_accuracy_m' => 5]);

        $response = $this->actingAs($this->admin)->get(route('reports.pdf', $this->reportParams()));

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $response->assertOk();
            $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
        } else {
            $response->assertSessionHas('error');
        }
    }

    public function test_photo_thumbnail_is_small_jpeg_data_uri(): void
    {
        $path = UploadedFile::fake()->image('bukti.jpg', 1200, 900)->store('visit-photos/1', 'local');

        $uri = PhotoThumb::dataUri($path, 200);

        $this->assertNotNull($uri);
        $this->assertStringStartsWith('data:image/jpeg;base64,', $uri);

        $size = getimagesizefromstring(base64_decode(substr($uri, strlen('data:image/jpeg;base64,'))));
        $this->assertLessThanOrEqual(200, max($size[0], $size[1]));
    }

    public function test_photo_thumbnail_returns_null_for_missing_file(): void
    {
        $this->assertNull(PhotoThumb::dataUri('visit-photos/tidak-ada.jpg'));
    }

    public function test_report_can_be_filtered_by_result_and_store(): void
    {
        $ok = $this->visit('Toko Berhasil', ['result' => 'successful']);
        $this->visit('Toko Tutup', ['result' => 'store_closed']);
        $this->visit('Toko Belum', ['result' => null, 'checked_in_at' => null]);

        $names = fn ($r) => $r->viewData('visits')->map(fn ($v) => $v->store->name)->sort()->values()->all();

        $this->assertSame(['Toko Tutup'], $names($this->actingAs($this->admin)->get(route('reports.index', $this->reportParams(['result' => 'store_closed'])))));
        $this->assertSame(['Toko Belum'], $names($this->actingAs($this->admin)->get(route('reports.index', $this->reportParams(['result' => 'pending'])))));
        $this->assertSame(['Toko Berhasil'], $names($this->actingAs($this->admin)->get(route('reports.index', $this->reportParams(['store_id' => $ok->store_id])))));
        $this->assertCount(3, $this->actingAs($this->admin)->get(route('reports.index', $this->reportParams(['result' => 'bukan-hasil-valid'])))->viewData('visits'));
    }

    public function test_invalid_dates_fall_back_and_reversed_range_is_swapped(): void
    {
        $this->visit('Toko Hari Ini');

        $r = $this->actingAs($this->admin)->get(route('reports.index', ['start_date' => 'bukan-tanggal', 'end_date' => '2999-99-99']));
        $r->assertOk();

        $r = $this->actingAs($this->admin)->get(route('reports.index', [
            'start_date' => Carbon::now()->addDay()->toDateString(),
            'end_date' => Carbon::now()->subDay()->toDateString(),
        ]));
        $this->assertCount(1, $r->viewData('visits'));
    }

    public function test_revenue_follows_the_result_filter(): void
    {
        $v = $this->visit('Toko Omzet', ['result' => 'successful']);
        \App\Models\Order::create(['route_visit_id' => $v->id, 'order_number' => 'PO-1', 'total_amount' => 150000]);
        $this->visit('Toko Tutup', ['result' => 'store_closed']);

        $all = $this->actingAs($this->admin)->get(route('reports.index', $this->reportParams()))->viewData('totalRevenue');
        $closed = $this->actingAs($this->admin)->get(route('reports.index', $this->reportParams(['result' => 'store_closed'])))->viewData('totalRevenue');

        $this->assertEquals(150000, $all);
        $this->assertEquals(0, $closed);
    }

    public function test_csv_export_has_bom_header_rows_and_respects_filters(): void
    {
        $this->visit('Toko Berhasil', ['result' => 'successful']);
        $this->visit('Toko Tutup', ['result' => 'store_closed']);

        $res = $this->actingAs($this->admin)->get(route('reports.csv', $this->reportParams(['result' => 'successful'])));
        $res->assertOk();
        $this->assertStringContainsString('.csv', $res->headers->get('content-disposition'));
        $body = $res->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $body);
        $this->assertStringContainsString('"Tanggal Rute";"Waktu Check-in"', $body);
        $this->assertStringContainsString('Toko Berhasil', $body);
        $this->assertStringNotContainsString('Toko Tutup', $body);
    }

    public function test_csv_neutralizes_formula_injection_and_is_admin_only(): void
    {
        $this->assertSame("'=HYPERLINK(\"x\")", \App\Http\Controllers\ReportController::safeCell('=HYPERLINK("x")'));
        $this->assertSame("'-1+1", \App\Http\Controllers\ReportController::safeCell('-1+1'));
        $this->assertSame('Toko Biasa', \App\Http\Controllers\ReportController::safeCell('Toko Biasa'));
        $this->assertSame(5, \App\Http\Controllers\ReportController::safeCell(5));

        $this->actingAs(User::find($this->sales->user_id))->get(route('reports.csv', $this->reportParams()))->assertRedirect(route('my-visits.index'));
    }
}
