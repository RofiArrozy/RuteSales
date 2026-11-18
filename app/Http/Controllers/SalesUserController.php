<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Sales;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SalesUserController extends Controller
{
    /**
     * Tampilkan daftar sales/admin.
     */
    public function index(Request $request)
    {
        $query = User::with('sales');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $users = $query->orderBy('role', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('sales.index', compact('users'));
    }

    /**
     * Tampilkan formulir tambah sales baru.
     */
    public function create()
    {
        return view('sales.create');
    }

    /**
     * Simpan sales baru ke database.
     */
    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->has('is_active') ? true : false;

        $user = User::create($data);

        // Jika user adalah sales, sinkronkan atau buat data profil sales
        if ($user->role === 'sales') {
            Sales::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'employee_code' => $user->employee_code,
                'status' => $user->is_active ? 'active' : 'inactive',
            ]);
        }

        return redirect()->route('sales.index')
            ->with('success', "Pengguna '{$user->name}' ({$user->role}) berhasil ditambahkan.");
    }

    /**
     * Tampilkan detail pengguna sales beserta daftar kunjungannya.
     */
    public function show($id)
    {
        $user = User::with(['sales.routes.visits.store', 'sales.routes.visits.order'])->findOrFail($id);

        return view('sales.show', compact('user'));
    }

    /**
     * Tampilkan formulir edit sales.
     */
    public function edit($id)
    {
        $user = User::with('sales')->findOrFail($id);

        return view('sales.edit', compact('user'));
    }

    /**
     * Perbarui data sales di database.
     */
    public function update(UserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = $request->has('is_active') ? true : false;

        $user->update($data);

        // Sinkronkan data ke profil sales jika ada
        if ($user->role === 'sales') {
            $sales = $user->sales ?? new Sales(['user_id' => $user->id]);
            $sales->name = $user->name;
            $sales->phone = $user->phone;
            $sales->employee_code = $user->employee_code;
            $sales->status = $user->is_active ? 'active' : 'inactive';
            $sales->save();
        }

        return redirect()->route('sales.index')
            ->with('success', "Data pengguna '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Hapus data sales dari database atau nonaktifkan jika memiliki riwayat.
     */
    public function destroy($id)
    {
        $user = User::with('sales.routes')->findOrFail($id);

        // Jangan izinkan menghapus diri sendiri jika sedang login
        if (auth()->check() && auth()->id() === $user->id) {
            return redirect()->route('sales.index')
                ->with('error', 'Anda tidak dapat menghapus akun yang sedang Anda gunakan.');
        }

        $name = $user->name;

        // Jika memiliki profil sales yang sudah memiliki riwayat rute, nonaktifkan
        if ($user->sales && $user->sales->routes()->exists()) {
            $user->update(['is_active' => false]);
            $user->sales->update(['status' => 'inactive']);

            return redirect()->route('sales.index')
                ->with('success', "Pengguna '{$name}' memiliki riwayat penugasan rute, sehingga statusnya dinonaktifkan.");
        }

        if ($user->sales) {
            $user->sales->delete();
        }
        $user->delete();

        return redirect()->route('sales.index')
            ->with('success', "Pengguna '{$name}' berhasil dihapus dari sistem.");
    }
}
