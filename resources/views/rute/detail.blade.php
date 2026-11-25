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

    <!-- Peta Rute (Leaflet + OSRM) -->
    <x-route-map :visits="$r->visits" />

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
                <div class="p-5 hover:bg-slate-50/50 transition-colors" x-data="{ showClarify: false }">
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
                                        <x-visit-photos :visit="$visitItem" />
                                        <x-visit-review :visit="$visitItem" />
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Aksi admin: hanya memantau; hasil dipertanyakan lewat klarifikasi -->
                        <div class="flex items-center gap-2 self-end md:self-center shrink-0">
                            @if(!$visitItem->isCheckedIn())
                                <span class="text-xs text-slate-400">Menunggu check-in sales</span>
                            @elseif($visitItem->hasOpenClarification())
                                <span class="text-xs font-semibold text-amber-800 bg-amber-100 border border-amber-200 px-3 py-1.5 rounded-xl">Menunggu jawaban sales</span>
                            @else
                                <button type="button" @click="showClarify = !showClarify"
                                        class="px-4 py-2 text-xs font-semibold rounded-xl transition-all bg-slate-100 text-slate-700 hover:bg-slate-200">
                                    <span x-show="!showClarify">Minta Klarifikasi</span>
                                    <span x-show="showClarify">Tutup</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    @if($visitItem->clarification_requested_at)
                        <div class="mt-4 text-xs p-3 rounded-lg border {{ $visitItem->hasOpenClarification() ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-slate-50 border-slate-200 text-slate-700' }}">
                            <div class="font-bold">Klarifikasi diminta {{ $visitItem->clarification_requested_at->translatedFormat('d M Y, H:i') }}</div>
                            <div class="mt-0.5">"{{ $visitItem->clarification_note }}"</div>
                            @if($visitItem->clarification_answered_at)
                                <div class="mt-1.5"><strong>Jawaban sales</strong> ({{ $visitItem->clarification_answered_at->translatedFormat('d M Y, H:i') }}): {{ $visitItem->clarification_response }}</div>
                            @else
                                <div class="mt-1.5 opacity-80">Belum dijawab sales.</div>
                            @endif
                        </div>
                    @endif

                    @if($visitItem->isCheckedIn() && !$visitItem->hasOpenClarification())
                        <!-- Formulir permintaan klarifikasi (admin tidak mengubah hasil check-in) -->
                        <div x-show="showClarify" x-cloak class="mt-4 pt-4 border-t border-slate-200/80">
                            <form action="{{ route('rute.clarify', $visitItem->id) }}" method="POST" data-lock-submit>
                                @csrf
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Hal yang perlu diklarifikasi <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="clarification_note" rows="2" required minlength="3" maxlength="500"
                                          placeholder="Contoh: Lokasi check-in jauh dari toko. Mohon jelaskan atau perbaiki."
                                          class="w-full px-3 py-2 bg-white rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/30"></textarea>
                                @error('clarification_note')
                                    <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                                <div class="flex justify-end mt-2">
                                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg shadow-sm">
                                        Kirim Permintaan Klarifikasi
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-slate-400">
                    Belum ada toko yang ditambahkan pada rute ini.
                </div>
            @endforelse
        </div>
    </div>
</x-layout>
