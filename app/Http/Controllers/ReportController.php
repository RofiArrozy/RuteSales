<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RouteVisit;
use App\Models\Sales;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Tampilkan rekapitulasi laporan kunjungan dan pesanan berdasarkan periode tanggal.
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $salesId = $request->input('sales_id');

        $query = RouteVisit::with(['salesRoute.sales', 'store', 'order'])
            ->whereHas('salesRoute', function ($q) use ($startDate, $endDate, $salesId) {
                $q->whereBetween('route_date', [$startDate, $endDate]);
                if ($salesId) {
                    $q->where('sales_id', $salesId);
                }
            });

        $visits = $query->orderBy('checked_in_at', 'desc')->paginate(15)->withQueryString();

        // Metrik Ringkasan dalam Periode
        $totalVisitsInPeriod = (clone $query)->count();
        $completedVisits = (clone $query)->whereNotNull('result')->count();
        $successfulVisits = (clone $query)->where('result', 'successful')->count();
        $totalRevenue = (float) Order::whereHas('routeVisit.salesRoute', function ($q) use ($startDate, $endDate, $salesId) {
            $q->whereBetween('route_date', [$startDate, $endDate]);
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
            'totalVisitsInPeriod',
            'completedVisits',
            'successfulVisits',
            'totalRevenue',
            'allSales'
        ));
    }
}
