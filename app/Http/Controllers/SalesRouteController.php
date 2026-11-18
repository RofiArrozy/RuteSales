<?php

namespace App\Http\Controllers;

use App\Models\RouteVisit;
use App\Models\Sales;
use App\Models\SalesRoute;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesRouteController extends Controller
{
    /**
     * Tampilkan daftar rute kunjungan sales.
     */
    public function index(Request $request)
    {
        $query = SalesRoute::with(['sales.user', 'visits.store', 'visits.order']);

        if ($salesId = $request->input('sales_id')) {
            $query->where('sales_id', $salesId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($date = $request->input('date')) {
            $query->whereDate('route_date', $date);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('sales', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('visits.store', function ($tq) use ($search) {
                      $tq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $routes = $query->orderBy('route_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $activeSales = Sales::active()->orderBy('name')->get();
        $todayDate = Carbon::today()->format('Y-m-d');

        return view('rute.index', compact('routes', 'activeSales', 'todayDate'));
    }

    /**
     * Tampilkan formulir pembuatan rute baru dengan pemilihan toko multi-stop.
     */
    public function create()
    {
        $activeSales = Sales::active()->orderBy('name')->get();
        $activeStores = Store::active()->orderBy('name')->get();

        return view('rute.create', compact('activeSales', 'activeStores'));
    }

    /**
     * Simpan rute baru beserta urutan kunjungan toko.
     */
    public function store(Request $request)
    {
        $request->validate([
            'sales_id' => ['required', 'exists:sales,id'],
            'route_date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'stores' => ['required', 'array', 'min:1'],
            'stores.*' => ['required', 'exists:stores,id'],
        ], [
            'sales_id.required' => 'Tenaga sales wajib dipilih.',
            'sales_id.exists' => 'Sales yang dipilih tidak valid.',
            'route_date.required' => 'Tanggal rute wajib diisi.',
            'name.required' => 'Nama rute kunjungan wajib diisi.',
            'stores.required' => 'Pilih minimal satu toko untuk dikunjungi.',
            'stores.min' => 'Pilih minimal satu toko untuk dikunjungi.',
        ]);

        // Validasi aturan bisnis: Sales harus aktif (TC-08)
        $sales = Sales::findOrFail($request->sales_id);
        if (!$sales->isActive()) {
            return back()->withErrors(['sales_id' => 'Sales yang dipilih berstatus nonaktif. Hanya sales aktif yang dapat ditugaskan.'])->withInput();
        }

        // Validasi aturan bisnis: Cegah duplikasi toko dalam rute (FR-07, TC-10)
        $storeIds = $request->input('stores');
        if (count($storeIds) !== count(array_unique($storeIds))) {
            return back()->withErrors(['stores' => 'Terdapat toko duplikat pada rute ini. Setiap toko hanya boleh dikunjungi satu kali per rute.'])->withInput();
        }

        // Validasi aturan bisnis: Semua toko yang dipilih harus aktif (TC-09)
        $inactiveStoresCount = Store::whereIn('id', $storeIds)->where('status', 'inactive')->count();
        if ($inactiveStoresCount > 0) {
            return back()->withErrors(['stores' => 'Salah satu atau lebih toko yang dipilih berstatus nonaktif. Hanya toko aktif yang dapat ditambahkan ke rute.'])->withInput();
        }

        $route = DB::transaction(function () use ($request, $storeIds) {
            $route = SalesRoute::create([
                'sales_id' => $request->sales_id,
                'route_date' => $request->route_date,
                'name' => $request->name,
                'status' => 'planned',
                'notes' => $request->notes,
            ]);

            foreach ($storeIds as $index => $storeId) {
                RouteVisit::create([
                    'sales_route_id' => $route->id,
                    'store_id' => $storeId,
                    'sequence' => $index + 1,
                    'notes' => null,
                ]);
            }

            return $route;
        });

        return redirect()->route('rute.detail', $route->id)
            ->with('success', "Rute kunjungan '{$route->name}' berhasil dibuat dengan " . count($storeIds) . " titik kunjungan.");
    }

    /**
     * Halaman Dinamis: /rute/detail/{id}
     * Menampilkan informasi rute, sales penanggung jawab, serta daftar toko berurutan.
     */
    public function show($id)
    {
        $route = SalesRoute::with(['sales.user', 'visits.store', 'visits.order'])->findOrFail($id);

        return view('rute.detail', compact('route'));
    }

    /**
     * Alias method detail untuk kompatibilitas persyaratan proyek.
     */
    public function detail($id)
    {
        return $this->show($id);
    }

    /**
     * Tampilkan formulir edit rute.
     */
    public function edit($id)
    {
        $route = SalesRoute::with(['visits.store'])->findOrFail($id);
        $activeSales = Sales::active()->orderBy('name')->get();
        $activeStores = Store::active()->orderBy('name')->get();

        return view('rute.edit', compact('route', 'activeSales', 'activeStores'));
    }

    /**
     * Perbarui data rute kunjungan.
     */
    public function update(Request $request, $id)
    {
        $route = SalesRoute::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'route_date' => ['required', 'date'],
            'status' => ['required', 'in:planned,in_progress,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $route->update($request->only(['name', 'route_date', 'status', 'notes']));

        return redirect()->route('rute.detail', $route->id)
            ->with('success', "Rute kunjungan '{$route->name}' berhasil diperbarui.");
    }

    /**
     * Hapus rute kunjungan.
     */
    public function destroy($id)
    {
        $route = SalesRoute::findOrFail($id);
        $name = $route->name;
        $route->delete();

        return redirect()->route('rute.index')
            ->with('success', "Rute '{$name}' berhasil dihapus.");
    }
}
