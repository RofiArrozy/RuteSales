@php
    $r = $route ?? ($visit instanceof \App\Models\SalesRoute ? $visit : $visit->salesRoute);
@endphp

<x-layout title="Detail Rute: {{ $r->name }}">
    <!-- Top Breadcrumb & Status -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-2">
                <a href="{{ route('rute.index') }}" class="text-blue-600 hover:text-blue-800">&larr; Kelola Rute</a>
                <span>/</span>
                <span class="text-slate-600 font-mono bg-slate-100 px-2 py-0.5 rounded">URI Dinamis: /rute/detail/{{ $r->id }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $r->name }}</h1>
                <x-badge :status="$r->status" />
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Jadwal Rute: <strong class="text-slate-700">{{ $r->route_date->translatedFormat('l, d F Y') }}</strong>
                &bull; Sales: <strong class="text-slate-700">{{ $r->sales->name ?? '-' }} ({{ $r->sales->employee_code ?? 'Sales' }})</strong>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('rute.edit', $r->id) }}" class="px-3.5 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold shadow-xs transition-all">
                Edit Rute
            </a>
            <form action="{{ route('rute.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus rute kunjungan ini?');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3 py-2 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-xl text-xs font-semibold transition-all">
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <!-- Ringkasan Progres Rute -->
    @php
        $totalStops = $r->visits->count();
        $completedStops = $r->visits->whereNotNull('result')->count();
        $totalRouteRevenue = $r->visits->sum(fn($v) => $v->order ? $v->order->total_amount : 0);
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-xs">
            <span class="text-xs text-slate-400 font-medium">Total Titik Singgah</span>
            <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $totalStops }} Toko</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-xs">
            <span class="text-xs text-slate-400 font-medium">Progres Kunjungan</span>
            <p class="text-xl font-bold text-blue-600 mt-0.5">{{ $completedStops }} dari {{ $totalStops }} Selesai</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-xs">
            <span class="text-xs text-slate-400 font-medium">Akumulasi Nilai Pesanan</span>
            <p class="text-xl font-bold text-emerald-600 mt-0.5">Rp {{ number_format($totalRouteRevenue, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Daftar Toko yang Harus Dikunjungi Berurutan (FR-06, FR-08, TC-11) -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-8">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    🏬
                </div>
                <div>
                    <h2 class="font-bold text-slate-900 text-base">Daftar Toko yang Harus Dikunjungi (Berdasarkan Urutan)</h2>
                    <p class="text-xs text-slate-400">Toko harus dikunjungi sesuai nomor urut sequence yang telah ditetapkan</p>
                </div>
            </div>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($r->visits as $visitItem)
                <div class="p-5 hover:bg-slate-50/50 transition-colors" x-data="{ showCheckinForm: false }">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <!-- Nomor Urut Kunjungan -->
                            <div class="w-10 h-10 rounded-xl {{ $visitItem->isCheckedIn() ? 'bg-emerald-600' : 'bg-blue-600' }} text-white font-extrabold flex items-center justify-center text-sm shrink-0 shadow-sm">
                                #{{ $visitItem->sequence }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2.5">
                                    <h3 class="text-base font-bold text-slate-900">
                                        {{ $visitItem->store->name ?? 'Toko' }}
                                    </h3>
                                    @if($visitItem->result)
                                        <x-badge :status="$visitItem->result" />
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                            Menunggu Kunjungan
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 mt-1 flex items-center gap-2">
                                    <span>👤 Pemilik: <strong>{{ $visitItem->store->owner_name ?? '-' }}</strong></span>
                                    &bull;
                                    <span>📞 {{ $visitItem->store->phone ?? '-' }}</span>
                                    &bull;
                                    <span>📍 {{ $visitItem->store->address ?? '-' }}</span>
                                </p>

                                @if($visitItem->isCheckedIn())
                                    <div class="mt-2.5 text-xs bg-slate-50 p-2.5 rounded-lg border border-slate-200/70 space-y-1">
                                        <div class="text-slate-600">
                                            ⏱ Waktu Check-in Server: <strong>{{ $visitItem->checked_in_at->translatedFormat('d M Y, H:i:s') }}</strong>
                                        </div>
                                        @if($visitItem->failure_reason)
                                            <div class="text-rose-600 font-medium">
                                                ⚠ Alasan Kendala: {{ $visitItem->failure_reason }}
                                            </div>
                                        @endif
                                        @if($visitItem->order)
                                            <div class="text-emerald-700 font-semibold">
                                                🛒 No. Pesanan: {{ $visitItem->order->order_number }} &bull; Nilai: Rp {{ number_format($visitItem->order->total_amount, 0, ',', '.') }}
                                            </div>
                                        @endif
                                        @if($visitItem->notes)
                                            <div class="text-slate-500 italic">
                                                Catatan: "{{ $visitItem->notes }}"
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Aksi / Form Checkin Trigger -->
                        <div class="flex items-center gap-2 self-end md:self-center shrink-0">
                            <button type="button" @click="showCheckinForm = !showCheckinForm" 
                                    class="px-4 py-2 text-xs font-semibold rounded-xl transition-all {{ $visitItem->isCheckedIn() ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs' }}">
                                <span x-show="!showCheckinForm">{{ $visitItem->isCheckedIn() ? 'Perbarui Check-in' : 'Catat Check-in' }}</span>
                                <span x-show="showCheckinForm">Tutup Formulir</span>
                            </button>
                        </div>
                    </div>

                    <!-- Formulir Check-in Inline Terproteksi CSRF & Server-Time -->
                    <div x-show="showCheckinForm" x-cloak class="mt-5 pt-4 border-t border-slate-200/80 bg-slate-50/80 -mx-5 -mb-5 p-5 rounded-b-2xl">
                        <form action="{{ route('rute.checkin', $visitItem->id) }}" method="POST" x-data="{ checkinResult: '{{ $visitItem->result ?? 'successful' }}' }">
                            @csrf
                            <h4 class="text-sm font-bold text-slate-800 mb-3">Formulir Check-in Lapangan (Waktu Server Otomatis)</h4>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Hasil Kunjungan <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="result" x-model="checkinResult" required
                                            class="w-full px-3 py-2 bg-white rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="successful">Berhasil (Closing / Pesanan)</option>
                                        <option value="store_closed">Toko Tutup</option>
                                        <option value="failed">Kunjungan Ditolak / Gagal</option>
                                    </select>
                                </div>

                                <!-- Nilai Pesanan (Jika Hasil Berhasil) -->
                                <div x-show="checkinResult === 'successful'">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Total Nilai Pesanan (Rp)
                                    </label>
                                    <input type="number" step="any" min="0" name="total_amount" 
                                           value="{{ $visitItem->order->total_amount ?? 0 }}"
                                           placeholder="0"
                                           class="w-full px-3 py-2 bg-white rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Alasan Kendala (Wajib Jika Gagal/Tutup) -->
                                <div class="md:col-span-2" x-show="checkinResult === 'store_closed' || checkinResult === 'failed'">
                                    <label class="block text-xs font-bold text-rose-700 uppercase tracking-wider mb-1.5">
                                        Alasan Kendala / Penolakan <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" name="failure_reason" 
                                           value="{{ $visitItem->failure_reason }}"
                                           placeholder="Tuliskan alasan mengapa toko tutup atau kunjungan ditolak..."
                                           :required="checkinResult === 'store_closed' || checkinResult === 'failed'"
                                           class="w-full px-3 py-2 bg-white rounded-lg border border-rose-300 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Catatan Tambahan
                                </label>
                                <textarea name="notes" rows="2" placeholder="Catatan hasil pembicaraan dengan pemilik toko..."
                                          class="w-full px-3 py-2 bg-white rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $visitItem->notes }}</textarea>
                            </div>

                            <div class="flex items-center justify-end gap-2">
                                <button type="button" @click="showCheckinForm = false" class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-200 rounded-lg">
                                    Batal
                                </button>
                                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm">
                                    Simpan Check-in
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400">
                    Belum ada toko yang ditambahkan pada rute ini.
                </div>
            @endforelse
        </div>
    </div>
</x-layout>
