<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RouteVisit;
use App\Models\Sales;
use App\Models\SalesRoute;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

        // Ringkasan Performa Sales
        $salesList = Sales::with(['routes.visits.order'])->get();
        $salesPerformance = $salesList->map(function ($s) {
            $visits = $s->routes->flatMap->visits;
            $completed = $visits->whereNotNull('result');
            $success = $visits->where('result', 'successful');
            $revenue = $success->sum(function ($v) {
                return $v->order ? (float) $v->order->total_amount : 0;
            });

            return (object) [
                'id' => $s->id,
                'name' => $s->name,
                'total_visits' => $visits->count(),
                'completed_visits' => $completed->count(),
                'success_visits' => $success->count(),
                'total_revenue' => $revenue,
            ];
        });

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
            'salesPerformance'
        ));
    }
}
