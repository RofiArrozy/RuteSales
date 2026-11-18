<x-layout title="Check-in Kunjungan: {{ $visit->store->name ?? 'Toko' }}">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('my-visits.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2">
                &larr; Kembali ke Kunjungan Saya
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900">Check-in Toko: {{ $visit->store->name }}</h1>
                @if($visit->result)
                    <x-badge :status="$visit->result" />
                @else
                    <span class="text-xs bg-slate-100 text-slate-600 px-2.5 py-0.5 rounded-full font-medium">Belum Check-in</span>
                @endif
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Rute: <strong class="text-slate-700">{{ $visit->salesRoute->name }}</strong> &bull; Titik Kunjungan ke-<strong>#{{ $visit->sequence }}</strong>
            </p>
        </div>

        <!-- Profil Toko Sasaran -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-3">Informasi Toko Sasaran</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Nama Outlet</span>
                    <strong class="text-slate-900 block text-base mt-0.5">{{ $visit->store->name }}</strong>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Pemilik / Kontak</span>
                    <span class="text-slate-800 block mt-0.5">{{ $visit->store->owner_name ?? '-' }} ({{ $visit->store->phone ?? 'Tidak ada telepon' }})</span>
                </div>
                <div class="sm:col-span-2">
                    <span class="text-xs text-slate-400 block font-medium">Alamat Lengkap</span>
                    <span class="text-slate-800 block mt-0.5">{{ $visit->store->address }}</span>
                </div>
            </div>
        </div>

        <!-- Form Check-in Transaksional (CSRF Protected & Server Time) -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Formulir Pelaporan Check-in Lapangan</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Waktu check-in akan otomatis dicatat dari stempel waktu server.</p>
                </div>
                <div class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xs rounded-lg font-mono font-medium">
                    Server Time: {{ date('H:i') }} WIB
                </div>
            </div>

            <form action="{{ route('my-visits.checkin', $visit->id) }}" method="POST" x-data="{
                result: '{{ old('result', $visit->result ?? 'successful') }}',
                orderAmount: '{{ old('total_amount', $visit->order->total_amount ?? '') }}'
            }">
                @csrf

                <div class="space-y-6">
                    <!-- Pilihan Hasil Kunjungan (FR-10, TC-13, TC-14, TC-15) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Pilih Hasil Kunjungan <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="result" value="successful" x-model="result" class="peer sr-only">
                                <div class="p-3.5 rounded-xl border border-slate-200 peer-checked:border-emerald-600 peer-checked:bg-emerald-50/40 peer-checked:text-emerald-900 text-center transition-all hover:bg-slate-50">
                                    <div class="font-bold text-sm">✓ Berhasil</div>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Toko buka &amp; closing pesanan</p>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="result" value="store_closed" x-model="result" class="peer sr-only">
                                <div class="p-3.5 rounded-xl border border-slate-200 peer-checked:border-amber-600 peer-checked:bg-amber-50/40 peer-checked:text-amber-900 text-center transition-all hover:bg-slate-50">
                                    <div class="font-bold text-sm">! Toko Tutup</div>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Rolling door tutup / libur</p>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="result" value="failed" x-model="result" class="peer sr-only">
                                <div class="p-3.5 rounded-xl border border-slate-200 peer-checked:border-rose-600 peer-checked:bg-rose-50/40 peer-checked:text-rose-900 text-center transition-all hover:bg-slate-50">
                                    <div class="font-bold text-sm">✕ Ditolak / Gagal</div>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Toko menolak pemesanan</p>
                                </div>
                            </label>
                        </div>
                        @error('result')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Input Nilai Pesanan (Hanya muncul jika successful) (FR-13, TC-17, TC-18, TC-19) -->
                    <div x-show="result === 'successful'" x-transition class="p-4 bg-emerald-50/50 rounded-xl border border-emerald-200/80 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🛒</span>
                            <h3 class="text-sm font-bold text-emerald-900">Pencatatan Pesanan Penjualan</h3>
                        </div>

                        <div>
                            <label for="total_amount" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Total Nominal Pesanan (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" step="any" min="0" name="total_amount" id="total_amount" 
                                   x-model="orderAmount"
                                   placeholder="Contoh: 1500000"
                                   class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('total_amount') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200 bg-white' }} text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            @error('total_amount')
                                <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                            <p class="text-[11px] text-slate-500 mt-1">Masukkan total nominal rupiah transaksi pesanan pada kunjungan ini.</p>
                        </div>
                    </div>

                    <!-- Input Alasan Kendala (Wajib jika toko tutup atau gagal) (FR-12, TC-14, TC-15) -->
                    <div x-show="result === 'store_closed' || result === 'failed'" x-transition class="p-4 bg-rose-50/40 rounded-xl border border-rose-200/80 space-y-2">
                        <label for="failure_reason" class="block text-xs font-bold text-rose-900 uppercase tracking-wider">
                            Alasan Kegagalan / Kendala Toko <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="failure_reason" id="failure_reason" rows="2"
                                  placeholder="Contoh: Pemilik sedang berada di luar kota, stok gudang toko masih penuh..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('failure_reason') ? 'border-rose-400 bg-white' : 'border-slate-200 bg-white' }} text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">{{ old('failure_reason', $visit->failure_reason) }}</textarea>
                        @error('failure_reason')
                            <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Catatan Tambahan -->
                    <div>
                        <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Catatan Kunjungan Lapangan
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                                  placeholder="Catatan tambahan mengenai kondisi toko atau masukan pemilik..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">{{ old('notes', $visit->notes) }}</textarea>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="{{ route('my-visits.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-all">
                            Simpan Hasil Check-in
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layout>
