<x-layout title="Profil Pengguna: {{ $user->name }}">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2">
                &larr; Kembali ke Tim Sales
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900">{{ $user->name }}</h1>
                <x-badge :status="$user->role" />
                <x-badge :status="$user->is_active ? 'active' : 'inactive'" />
            </div>
            <p class="text-sm text-slate-500 mt-1">Kode Pegawai: {{ $user->employee_code ?? '-' }} | Email: {{ $user->email }} | HP: {{ $user->phone ?? '-' }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('quick.login', $user->id) }}" class="px-3.5 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-xl text-xs font-semibold shadow-xs transition-all">
                Masuk Sebagai Akun Ini
            </a>
            <a href="{{ route('sales.edit', $user->id) }}" class="px-3.5 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold shadow-xs transition-all">
                Edit Profil
            </a>
        </div>
    </div>

    <!-- Assigned Routes & Visits List -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Riwayat Penugasan Rute</h3>
                <p class="text-xs text-slate-400 mt-0.5">Daftar agenda rute yang ditugaskan kepada {{ $user->name }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Nama Rute</th>
                        <th class="px-5 py-3.5">Status Rute</th>
                        <th class="px-5 py-3.5">Jumlah Toko</th>
                        <th class="px-5 py-3.5">Total Omzet</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                        $userRoutes = $user->sales ? $user->sales->routes : collect();
                    @endphp
                    @forelse($userRoutes as $rute)
                        @php
                            $revenue = $rute->visits->sum(fn($v) => $v->order ? $v->order->total_amount : 0);
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-3.5 font-medium text-slate-800 whitespace-nowrap">
                                {{ $rute->route_date->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-slate-900">
                                {{ $rute->name }}
                            </td>
                            <td class="px-5 py-3.5">
                                <x-badge :status="$rute->status" />
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">
                                {{ $rute->visits->count() }} Toko
                            </td>
                            <td class="px-5 py-3.5 font-semibold">
                                @if($revenue > 0)
                                    <span class="text-emerald-600">Rp {{ number_format($revenue, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <a href="{{ route('rute.detail', $rute->id) }}" class="inline-flex px-2.5 py-1 text-xs font-semibold bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg">
                                    Detail Rute
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                Belum ada rute kunjungan yang ditugaskan kepada pengguna ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layout>
