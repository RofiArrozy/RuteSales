<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RouteVisit;
use App\Models\Sales;
use App\Support\PhotoThumb;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
        [$startDate, $endDate, $salesId, $flagged] = $this->filters($request);

        $base = $this->baseQuery($startDate, $endDate, $salesId);

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
        $totalRevenue = (float) Order::whereHas('routeVisit.salesRoute', function ($q) use ($startDate, $endDate, $salesId) {
            $q->whereDate('route_date', '>=', $startDate)->whereDate('route_date', '<=', $endDate);
            if ($salesId) {
                $q->where('sales_id', $salesId);
            }
        })->sum('total_amount');

        $allSales = Sales::orderBy('name')->get();

        return view('reports.index', compact(
            'visits',
            'startDate',
            'endDate',
            'salesId',
            'flagged',
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

        [$startDate, $endDate, $salesId, $flagged] = $this->filters($request);
        $withPhotos = $request->boolean('photos');

        $base = $this->baseQuery($startDate, $endDate, $salesId);

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
            'revenue' => (float) Order::whereHas('routeVisit.salesRoute', function ($q) use ($startDate, $endDate, $salesId) {
                $q->whereDate('route_date', '>=', $startDate)->whereDate('route_date', '<=', $endDate);
                if ($salesId) {
                    $q->where('sales_id', $salesId);
                }
            })->sum('total_amount'),
        ];

        $salesName = $salesId ? optional(Sales::find($salesId))->name : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf', [
            'visits' => $visits,
            'thumbs' => $thumbs,
            'summary' => $summary,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'salesName' => $salesName,
            'flagged' => $flagged,
            'withPhotos' => $withPhotos,
            'truncatedRows' => max(0, $totalRows - self::PDF_MAX_ROWS),
            'photoLimit' => self::PDF_MAX_PHOTOS,
            'generatedAt' => Carbon::now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("laporan-kunjungan_{$startDate}_sd_{$endDate}.pdf");
    }

    /**
     * Baca filter periode, sales, dan "perlu ditinjau" dari request.
     */
    private function filters(Request $request): array
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $salesId = $request->input('sales_id');
        $flagged = $request->boolean('flagged');

        return [$startDate, $endDate, $salesId, $flagged];
    }

    private function baseQuery(string $startDate, string $endDate, $salesId)
    {
        return RouteVisit::query()->whereHas('salesRoute', function ($q) use ($startDate, $endDate, $salesId) {
            $q->whereDate('route_date', '>=', $startDate)->whereDate('route_date', '<=', $endDate);
            if ($salesId) {
                $q->where('sales_id', $salesId);
            }
        });
    }
}
