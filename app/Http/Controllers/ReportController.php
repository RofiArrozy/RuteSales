<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RouteVisit;
use App\Models\Sales;
use App\Models\Store;
use App\Support\PhotoThumb;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /** Batas baris dan foto pada ekspor PDF agar tidak kehabisan memori. */
    private const PDF_MAX_ROWS = 500;
    private const PDF_MAX_PHOTOS = 100;

    /**
     * Tampilkan rekapitulasi laporan kunjungan dan pesanan berdasarkan periode tanggal.
     */
    public function index(Request $request)
    {
        [$startDate, $endDate, $salesId, $flagged, $result, $storeId] = $this->filters($request);

        $base = $this->baseQuery($startDate, $endDate, $salesId, $result, $storeId);

        $tableQuery = (clone $base)->with(['salesRoute.sales', 'store', 'order', 'photos', 'auditLogs']);
        if ($flagged) {
            $tableQuery->flagged();
        }
        $visits = $tableQuery->orderBy('checked_in_at', 'desc')->paginate(15)->withQueryString();

        // Metrik Ringkasan dalam Periode (tidak terpengaruh filter "perlu ditinjau")
        $totalVisitsInPeriod = (clone $base)->count();
        $completedVisits = (clone $base)->whereNotNull('result')->count();
        $successfulVisits = (clone $base)->where('result', 'successful')->count();
        $flaggedVisits = (clone $base)->flagged()->count();
        $totalRevenue = (float) Order::whereIn('route_visit_id', (clone $base)->select('route_visits.id'))->sum('total_amount');

        $allSales = Sales::orderBy('name')->get();
        $allStores = Store::orderBy('name')->get(['id', 'name']);

        return view('reports.index', compact(
            'visits',
            'startDate',
            'endDate',
            'salesId',
            'flagged',
            'result',
            'storeId',
            'allStores',
            'totalVisitsInPeriod',
            'completedVisits',
            'successfulVisits',
            'flaggedVisits',
            'totalRevenue',
            'allSales'
        ));
    }

    /**
     * Unduh laporan periode yang sama dalam format PDF (opsional dengan thumbnail foto bukti).
     */
    public function pdf(Request $request)
    {
        // Paket PDF dipasang terpisah: composer require barryvdh/laravel-dompdf
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return back()->with('error', 'Paket PDF belum terpasang. Jalankan: composer require barryvdh/laravel-dompdf');
        }

        [$startDate, $endDate, $salesId, $flagged, $result, $storeId] = $this->filters($request);
        $withPhotos = $request->boolean('photos');

        $base = $this->baseQuery($startDate, $endDate, $salesId, $result, $storeId);

        $rowsQuery = (clone $base)->with(['salesRoute.sales', 'store', 'order', 'auditLogs']);
        if ($withPhotos) {
            $rowsQuery->with('photos');
        }
        if ($flagged) {
            $rowsQuery->flagged();
        }

        $totalRows = (clone $rowsQuery)->count();
        $visits = $rowsQuery->orderBy('checked_in_at', 'desc')->limit(self::PDF_MAX_ROWS)->get();

        // Thumbnail foto pertama tiap kunjungan, dibatasi jumlahnya
        $thumbs = [];
        if ($withPhotos) {
            foreach ($visits->take(self::PDF_MAX_PHOTOS) as $visit) {
                $first = $visit->photos->first();
                if ($first) {
                    $thumbs[$visit->id] = PhotoThumb::dataUri($first->path);
                }
            }
        }

        $summary = [
            'total' => (clone $base)->count(),
            'completed' => (clone $base)->whereNotNull('result')->count(),
            'successful' => (clone $base)->where('result', 'successful')->count(),
            'flagged' => (clone $base)->flagged()->count(),
            'revenue' => (float) Order::whereIn('route_visit_id', (clone $base)->select('route_visits.id'))->sum('total_amount'),
        ];

        $salesName = $salesId ? optional(Sales::find($salesId))->name : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf', [
            'visits' => $visits,
            'thumbs' => $thumbs,
            'summary' => $summary,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'salesName' => $salesName,
            'storeName' => $storeId ? optional(Store::find($storeId))->name : null,
            'resultLabel' => ['successful' => 'Berhasil', 'store_closed' => 'Toko tutup', 'failed' => 'Gagal', 'pending' => 'Belum check-in'][$result] ?? null,
            'flagged' => $flagged,
            'withPhotos' => $withPhotos,
            'truncatedRows' => max(0, $totalRows - self::PDF_MAX_ROWS),
            'photoLimit' => self::PDF_MAX_PHOTOS,
            'generatedAt' => Carbon::now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("laporan-kunjungan_{$startDate}_sd_{$endDate}.pdf");
    }

    /**
     * Unduh data laporan (filter yang sama) sebagai CSV untuk Excel/Google Sheets.
     * Pemisah titik koma dan BOM UTF-8 agar terbuka rapi di Excel berlokal Indonesia.
     */
    public function csv(Request $request): StreamedResponse
    {
        [$startDate, $endDate, $salesId, $flagged, $result, $storeId] = $this->filters($request);

        $query = $this->baseQuery($startDate, $endDate, $salesId, $result, $storeId)
            ->with(['salesRoute.sales', 'store', 'order', 'auditLogs'])
            ->withCount('photos');
        if ($flagged) {
            $query->flagged();
        }

        $resultLabels = ['successful' => 'Berhasil', 'store_closed' => 'Toko tutup', 'failed' => 'Gagal'];
        $gpsLabels = ['near' => 'Dekat', 'far' => 'Jauh', 'unknown' => 'Tidak terverifikasi'];

        return response()->streamDownload(function () use ($query, $resultLabels, $gpsLabels) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Tanggal Rute', 'Waktu Check-in', 'Nama Rute', 'Sales', 'Toko', 'Alamat Toko', 'Hasil',
                'Nominal Pesanan', 'No. Pesanan', 'Alasan Kendala', 'Status GPS', 'Jarak GPS (m)',
                'Alasan Lompat Urutan', 'Klarifikasi Admin', 'Jawaban Sales', 'Jumlah Foto', 'Jumlah Perubahan',
            ], ';');

            $query->orderBy('checked_in_at', 'desc')->chunk(200, function ($chunk) use ($out, $resultLabels, $gpsLabels) {
                foreach ($chunk as $v) {
                    fputcsv($out, array_map([self::class, 'safeCell'], [
                        optional($v->salesRoute->route_date)->format('Y-m-d'),
                        optional($v->checked_in_at)->format('Y-m-d H:i'),
                        $v->salesRoute->name ?? '',
                        $v->salesRoute->sales->name ?? '',
                        $v->store->name ?? '',
                        $v->store->address ?? '',
                        $v->result ? ($resultLabels[$v->result] ?? $v->result) : 'Belum check-in',
                        $v->order ? (string) $v->order->total_amount : '',
                        $v->order->order_number ?? '',
                        $v->failure_reason ?? '',
                        $v->checked_in_at ? ($gpsLabels[$v->gpsStatus()] ?? '') : '',
                        $v->gps_distance_m ?? '',
                        $v->skip_reason ?? '',
                        $v->clarification_note ?? '',
                        $v->clarification_response ?? '',
                        $v->photos_count,
                        $v->auditLogs->where('action', 'checkin_updated')->count(),
                    ]), ';');
                }
            });

            fclose($out);
        }, "laporan-kunjungan_{$startDate}_sd_{$endDate}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Cegah CSV injection: sel teks yang diawali = + - @ dianggap rumus oleh Excel. */
    public static function safeCell($value)
    {
        return is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)
            ? "'" . $value
            : $value;
    }

    /**
     * Baca filter dari request: periode, sales, "perlu ditinjau", hasil, dan toko.
     * Tanggal tidak valid kembali ke bawaan; rentang terbalik ditukar.
     */
    private function filters(Request $request): array
    {
        $valid = fn ($v) => is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) !== false;

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $startDate = $valid($startDate) ? $startDate : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $valid($endDate) ? $endDate : Carbon::now()->format('Y-m-d');
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $salesId = $request->input('sales_id');
        $salesId = is_numeric($salesId) ? (int) $salesId : null;
        $storeId = $request->input('store_id');
        $storeId = is_numeric($storeId) ? (int) $storeId : null;
        $result = $request->input('result');
        $result = in_array($result, ['successful', 'store_closed', 'failed', 'pending'], true) ? $result : null;

        return [$startDate, $endDate, $salesId, $request->boolean('flagged'), $result, $storeId];
    }

    private function baseQuery(string $startDate, string $endDate, $salesId, ?string $result = null, ?int $storeId = null)
    {
        $query = RouteVisit::query()->whereHas('salesRoute', function ($q) use ($startDate, $endDate, $salesId) {
            $q->whereDate('route_date', '>=', $startDate)->whereDate('route_date', '<=', $endDate);
            if ($salesId) {
                $q->where('sales_id', $salesId);
            }
        });

        if ($storeId) {
            $query->where('store_id', $storeId);
        }
        if ($result === 'pending') {
            $query->whereNull('result');
        } elseif ($result) {
            $query->where('result', $result);
        }

        return $query;
    }
}
