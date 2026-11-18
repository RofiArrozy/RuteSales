<x-layout title="Laporan Rekapitulasi Kunjungan & Omzet">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Laporan Rekapitulasi Operasional</h1>
            <p class="text-sm text-slate-500 mt-1">Pemantauan efektivitas rute, closing transaksi, dan omzet per periode tanggal.</p>
        </div>
    </div>

    <!-- Filter Periode & Sales -->
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs mb-8">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
            <div class="sm:col-span-3">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" 
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="sm:col-span-3">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" 
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="sm:col-span-4">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Filter Tenaga Sales</label>
                <select name="sales_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Sales</option>
                    @foreach($allSales as $s)
                        <option value="{{ $s->id }}" {{ $salesId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-xs transition-colors">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
            <span class="text-xs font-medium text-slate-400">Total Kunjungan Dijadwalkan</span>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($totalVisitsInPeriod) }}</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
            <span class="text-xs font-medium text-slate-400">Kunjungan Selesai Check-in</span>
            <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ number_format($completedVisits) }}</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
            <span class="text-xs font-medium text-slate-400">Kunjungan Berhasil (Closing)</span>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($successfulVisits) }}</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
            <span class="text-xs font-medium text-slate-400">Total Omzet Pesanan</span>
            <p class="text-2xl font-extrabold text-emerald-700 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Table of Visits -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-900 text-base">Rincian Riwayat Kunjungan &amp; Pesanan</h2>
            <span class="text-xs text-slate-400">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3.5">Waktu Check-in</th>
                        <th class="px-5 py-3.5">Nama Rute</th>
                        <th class="px-5 py-3.5">Sales</th>
                        <th class="px-5 py-3.5">Toko / Warung</th>
                        <th class="px-5 py-3.5">Hasil</th>
                        <th class="px-5 py-3.5">Nominal Pesanan</th>
                        <th class="px-5 py-3.5">Alasan Kendala</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($visits as $v)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-3.5 text-xs text-slate-500 whitespace-nowrap">
                                {{ $v->checked_in_at ? $v->checked_in_at->translatedFormat('d M Y, H:i') : '-' }}
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-800">
                                <a href="{{ route('rute.detail', $v->sales_route_id) }}" class="hover:text-blue-600">
                                    {{ $v->salesRoute->name ?? '-' }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-slate-700">
                                {{ $v->salesRoute->sales->name ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-800">
                                {{ $v->store->name ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5">
                                @if($v->result)
                                    <x-badge :status="$v->result" />
                                @else
                                    <span class="text-xs text-slate-400">Belum</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-slate-900">
                                @if($v->order)
                                    <span class="text-emerald-600">Rp {{ number_format($v->order->total_amount, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500">
                                {{ $v->failure_reason ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-slate-400">
                                Tidak ada data kunjungan pada rentang tanggal yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($visits->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $visits->links() }}
            </div>
        @endif
    </div>
</x-layout>
