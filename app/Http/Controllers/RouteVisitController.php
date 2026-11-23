<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RouteVisit;
use App\Models\SalesRoute;
use App\Models\VisitPhoto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RouteVisitController extends Controller
{
    /**
     * Halaman Kunjungan Saya: menampilkan daftar tugas rute dan toko khusus untuk sales login.
     */
    public function myVisits(Request $request)
    {
        $user = Auth::user();

        // Pastikan pengguna memiliki profil sales terkait
        if ($user->isAdmin() && !$user->sales) {
            return redirect()->route('rute.index')
                ->with('info', 'Anda login sebagai Admin. Anda dapat memantau seluruh rute melalui menu Kelola Rute.');
        }

        $sales = $user->sales;
        if (!$sales) {
            abort(403, 'Akun Anda belum terhubung dengan profil sales aktif.');
        }

        $query = SalesRoute::with(['visits.store', 'visits.order'])
            ->where('sales_id', $sales->id);

        if ($date = $request->input('date')) {
            $query->whereDate('route_date', $date);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $routes = $query->orderBy('route_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $todayDate = Carbon::today()->format('Y-m-d');

        return view('route-visits.index', compact('routes', 'todayDate', 'sales'));
    }

    /**
     * Detail titik kunjungan toko pada rute tertentu beserta form check-in.
     */
    public function show($id)
    {
        $visit = RouteVisit::with(['salesRoute.sales.user', 'salesRoute.visits.store', 'store', 'order', 'photos'])->findOrFail($id);

        // Otorisasi kepemilikan data (TC-16): Sales hanya boleh melihat rute miliknya
        $user = Auth::user();
        if ($user && $user->isSales()) {
            $salesOwnerUserId = $visit->salesRoute->sales->user_id ?? null;
            if ($salesOwnerUserId !== $user->id) {
                abort(403, 'Akses ditolak. Anda tidak berhak melihat atau mengakses kunjungan sales lain.');
            }
        }

        return view('route-visits.show', compact('visit'));
    }

    /**
     * Proses Check-in Kunjungan Lapangan & Pencatatan Pesanan (Server-side validation & Server Time).
     */
    public function checkin(Request $request, $id)
    {
        $visit = RouteVisit::with(['salesRoute.sales', 'store', 'order', 'photos'])->findOrFail($id);

        // Otorisasi kepemilikan data (TC-16)
        $user = Auth::user();
        if ($user && $user->isSales()) {
            $salesOwnerUserId = $visit->salesRoute->sales->user_id ?? null;
            if ($salesOwnerUserId !== $user->id) {
                abort(403, 'Akses ditolak. Anda tidak berwenang mencatat check-in pada kunjungan sales lain.');
            }
        }

        // Aturan validasi input
        $rules = [
            'result' => ['required', 'in:successful,store_closed,failed'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'simulated_latitude' => ['nullable', 'numeric'],
            'simulated_longitude' => ['nullable', 'numeric'],
            // Foto bukti kunjungan (1-3 foto, jumlah akhir dicek di bawah bersama foto lama)
            'photos' => ['nullable', 'array', 'max:3'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_photos' => ['nullable', 'array'],
            'remove_photos.*' => ['integer'],
        ];

        // Validasi kegagalan / toko tutup wajib mengisi alasan (TC-14, TC-15)
        if (in_array($request->input('result'), ['store_closed', 'failed'])) {
            $rules['failure_reason'] = ['required', 'string', 'min:3', 'max:500'];
        } else {
            // Jika berhasil dan membuat pesanan, nominal harus valid dan >= 0 (TC-19)
            $rules['total_amount'] = ['nullable', 'numeric', 'min:0'];
            $rules['order_number'] = ['nullable', 'string', 'max:100'];
        }

        $messages = [
            'result.required' => 'Hasil kunjungan wajib dipilih.',
            'result.in' => 'Hasil kunjungan harus berupa successful, store_closed, atau failed.',
            'failure_reason.required' => 'Alasan wajib diisi jika kunjungan gagal atau toko tutup.',
            'failure_reason.min' => 'Alasan minimal 3 karakter.',
            'total_amount.numeric' => 'Nilai pesanan harus berupa angka yang valid.',
            'total_amount.min' => 'Nilai pesanan tidak boleh bernilai negatif.',
            'photos.max' => 'Maksimal 3 foto bukti per kunjungan.',
            'photos.*.image' => 'Bukti harus berupa file gambar.',
            'photos.*.mimes' => 'Foto harus berformat JPG, PNG, atau WebP.',
            'photos.*.max' => 'Ukuran setiap foto maksimal 5 MB.',
            'photos.*.uploaded' => 'Foto gagal diunggah. Ukuran file mungkin melebihi batas server.',
        ];

        $validated = $request->validate($rules, $messages);

        // Cegah pesanan jika hasil gagal (TC-18)
        if ($request->input('result') !== 'successful' && $request->filled('total_amount') && (float)$request->input('total_amount') > 0) {
            return back()->withErrors(['total_amount' => 'Pesanan hanya dapat dicatat jika hasil kunjungan berhasil (successful).'])->withInput();
        }

        // Foto bukti wajib: minimal 1, maksimal 3 foto (foto lama yang tidak dihapus ikut dihitung)
        $removeIds = collect($request->input('remove_photos', []))->map(fn ($v) => (int) $v)->all();
        $photosToRemove = $visit->photos->whereIn('id', $removeIds);
        $remainingCount = $visit->photos->count() - $photosToRemove->count();
        $uploads = array_values(array_filter($request->file('photos', [])));
        $totalPhotos = $remainingCount + count($uploads);

        if ($totalPhotos < 1) {
            return back()->withErrors(['photos' => 'Foto bukti wajib diunggah minimal 1 foto (berhasil, toko tutup, maupun ditolak).'])->withInput();
        }
        if ($totalPhotos > 3) {
            return back()->withErrors(['photos' => 'Maksimal 3 foto bukti per kunjungan. Hapus foto lama atau kurangi unggahan.'])->withInput();
        }

        // Simpan file ke disk private lebih dulu; jika transaksi gagal, file dibersihkan kembali
        $storedPaths = [];
        try {
            foreach ($uploads as $file) {
                $storedPaths[] = $file->store("visit-photos/{$visit->id}", 'local');
            }

        DB::transaction(function () use ($visit, $request, $validated, $storedPaths, $photosToRemove) {
            foreach ($storedPaths as $path) {
                VisitPhoto::create(['route_visit_id' => $visit->id, 'path' => $path]);
            }
            foreach ($photosToRemove as $old) {
                $old->delete();
            }

            // Catat check-in dengan waktu server (FR-11, TC-13)
            $visit->update([
                'result' => $validated['result'],
                'checked_in_at' => Carbon::now(),
                'notes' => $validated['notes'] ?? null,
                'failure_reason' => in_array($validated['result'], ['store_closed', 'failed']) ? $validated['failure_reason'] : null,
                'simulated_latitude' => $request->input('simulated_latitude'),
                'simulated_longitude' => $request->input('simulated_longitude'),
            ]);

            // Jika kunjungan berhasil dan ada nilai pesanan (atau default 0 jika berhasil dicatat)
            if ($validated['result'] === 'successful') {
                $amount = (float) $request->input('total_amount', 0);
                if ($amount >= 0) {
                    $orderNumber = $request->input('order_number') ?: ('ORD-' . date('Ymd') . '-' . sprintf('%04d', $visit->id));
                    Order::updateOrCreate(
                        ['route_visit_id' => $visit->id],
                        [
                            'order_number' => $orderNumber,
                            'total_amount' => $amount,
                            'notes' => $validated['notes'] ?? null,
                        ]
                    );
                }
            } else {
                // Jika status gagal/tutup, pastikan tidak ada pesanan tertinggal
                if ($visit->order) {
                    $visit->order->delete();
                }
            }

            // Evaluasi status rute induk (planned -> in_progress / completed)
            $route = $visit->salesRoute;
            $allVisitsCount = $route->visits()->count();
            $checkedVisitsCount = $route->visits()->whereNotNull('checked_in_at')->count();

            if ($checkedVisitsCount === $allVisitsCount && $allVisitsCount > 0) {
                $route->update(['status' => 'completed']);
            } elseif ($checkedVisitsCount > 0) {
                $route->update(['status' => 'in_progress']);
            }
        });
        } catch (\Throwable $e) {
            foreach ($storedPaths as $path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }

        // Transaksi sukses: hapus file fisik dari foto lama yang dihapus
        foreach ($photosToRemove as $old) {
            Storage::disk('local')->delete($old->path);
        }

        $message = "Check-in kunjungan ke '{$visit->store->name}' berhasil dicatat dengan status {$visit->result}.";
        if ($visit->result === 'successful' && $visit->order) {
            $message .= ' Nilai pesanan: Rp ' . number_format($visit->order->total_amount, 0, ',', '.');
        }

        // Redirect sesuai konteks (sales ke halaman kunjungan, atau rute detail)
        if (Auth::user() && Auth::user()->isSales()) {
            return redirect()->route('my-visits.index')->with('success', $message);
        }

        return redirect()->route('rute.detail', $visit->sales_route_id)->with('success', $message);
    }

    /**
     * Tampilkan file foto bukti kunjungan (private, hanya admin atau sales pemilik kunjungan).
     */
    public function photo($id)
    {
        $photo = VisitPhoto::with('routeVisit.salesRoute.sales')->findOrFail($id);

        $user = Auth::user();
        if ($user && $user->isSales()) {
            $ownerUserId = $photo->routeVisit->salesRoute->sales->user_id ?? null;
            if ($ownerUserId !== $user->id) {
                abort(403, 'Akses ditolak. Anda tidak berhak melihat foto kunjungan sales lain.');
            }
        }

        abort_unless(Storage::disk('local')->exists($photo->path), 404);

        return Storage::disk('local')->response($photo->path, null, [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
