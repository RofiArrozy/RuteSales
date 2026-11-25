<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RouteVisit;
use App\Models\Sales;
use App\Models\SalesRoute;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman Dashboard Admin dengan metrik ringkasan dari basis data aktual.
     */
    public function index(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');

        // Metrik Wajib 1: Total Kunjungan Selesai (FR-15, TC-20, TC-21)
        $totalCompletedVisits = RouteVisit::whereNotNull('result')->count();

        // Rincian Hasil Kunjungan: Successful, Store Closed, Failed
        $totalSuccessVisits = RouteVisit::where('result', 'successful')->count();
        $totalClosedVisits = RouteVisit::where('result', 'store_closed')->count();
        $totalRejectedVisits = RouteVisit::where('result', 'failed')->count();

        // Metrik Wajib 2: Persentase Keberhasilan Kunjungan (%) dengan penanganan pembagian nol (TC-21)
        $successPercentage = $totalCompletedVisits > 0
            ? round(($totalSuccessVisits / $totalCompletedVisits) * 100, 1)
            : 0;

        // Metrik Wajib 3: Total Nilai Pesanan (Rp) dari tabel orders
        $totalOrderValue = (float) Order::sum('total_amount');

        // Metrik Tambahan Operasional
        $totalStores = Store::count();
        $activeSalesCount = Sales::active()->count();
        $totalPlannedVisits = RouteVisit::whereNull('result')->count();
        $todayRoutesCount = SalesRoute::whereDate('route_date', $today)->count();

        // Daftar Kunjungan Terkini
        $recentVisits = RouteVisit::with(['salesRoute.sales', 'store', 'order'])
            ->whereNotNull('checked_in_at')
            ->orderBy('checked_in_at', 'desc')
            ->limit(8)
            ->get();

        // Ringkasan Performa Sales: satu query agregat (bukan memuat semua data ke memori)
        $salesPerformance = DB::table('sales')
            ->leftJoin('sales_routes', 'sales_routes.sales_id', '=', 'sales.id')
            ->leftJoin('route_visits', 'route_visits.sales_route_id', '=', 'sales_routes.id')
            ->leftJoin('orders', function ($join) {
                $join->on('orders.route_visit_id', '=', 'route_visits.id')
                    ->where('route_visits.result', '=', 'successful');
            })
            ->groupBy('sales.id', 'sales.name')
            ->orderBy('sales.id')
            ->selectRaw('sales.id, sales.name,
                COUNT(route_visits.id) AS total_visits,
                COALESCE(SUM(CASE WHEN route_visits.result IS NOT NULL THEN 1 ELSE 0 END), 0) AS completed_visits,
                COALESCE(SUM(CASE WHEN route_visits.result = \'successful\' THEN 1 ELSE 0 END), 0) AS success_visits,
                COALESCE(SUM(orders.total_amount), 0) AS total_revenue')
            ->get()
            ->map(fn ($r) => (object) [
                'id' => $r->id,
                'name' => $r->name,
                'total_visits' => (int) $r->total_visits,
                'completed_visits' => (int) $r->completed_visits,
                'success_visits' => (int) $r->success_visits,
                'total_revenue' => (float) $r->total_revenue,
            ]);

        // Kunjungan yang perlu ditinjau admin bulan ini (lokasi jauh, lompat urutan, check-in diperbarui)
        $monthStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $flaggedCount = RouteVisit::flagged()
            ->whereHas('salesRoute', fn ($q) => $q->whereDate('route_date', '>=', $monthStart)
                ->whereDate('route_date', '<=', $today))
            ->count();

        return view('dashboard.index', compact(
            'totalCompletedVisits',
            'successPercentage',
            'totalOrderValue',
            'totalSuccessVisits',
            'totalClosedVisits',
            'totalRejectedVisits',
            'totalStores',
            'activeSalesCount',
            'totalPlannedVisits',
            'todayRoutesCount',
            'recentVisits',
            'salesPerformance',
            'flaggedCount'
        ));
    }
}
