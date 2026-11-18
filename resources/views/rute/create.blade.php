<x-layout title="Buat Rute Kunjungan Multi-Toko">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <a href="{{ route('rute.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2">
                    &larr; Kembali ke Kelola Rute
                </a>
                <h1 class="text-2xl font-bold text-slate-900">Buat Rute Penugasan Baru</h1>
                <p class="text-sm text-slate-500 mt-1">Tugaskan sales untuk mengunjungi serangkaian toko mitra dalam urutan kunjungan yang terstruktur.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <form action="{{ route('rute.store') }}" method="POST" x-data="{
                stops: [
                    @if(old('stores'))
                        @foreach(old('stores') as $oldStoreId)
                            { id: {{ $oldStoreId }} },
                        @endforeach
                    @else
                        { id: '' }
                    @endif
                ],
                addStop() {
                    this.stops.push({ id: '' });
                },
                removeStop(index) {
                    if (this.stops.length > 1) {
                        this.stops.splice(index, 1);
                    }
                }
            }">
                @csrf

                <div class="space-y-6">
                    <!-- Nama Rute -->
                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Nama Rute Kunjungan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               placeholder="Contoh: Rute Harian Gejayan & Malioboro"
                               class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('name') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @error('name')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Pilih Sales Aktif -->
                        <div>
                            <label for="sales_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Tenaga Sales Penanggung Jawab <span class="text-rose-500">*</span>
                            </label>
                            <select name="sales_id" id="sales_id" required
                                    class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('sales_id') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                <option value="">-- Pilih Sales Aktif --</option>
                                @foreach($activeSales as $sales)
                                    <option value="{{ $sales->id }}" {{ old('sales_id') == $sales->id ? 'selected' : '' }}>
                                        {{ $sales->name }} ({{ $sales->employee_code ?? 'Sales' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('sales_id')
                                <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal Rute Kunjungan -->
                        <div>
                            <label for="route_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Tanggal Rencana Rute <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="route_date" id="route_date" value="{{ old('route_date', date('Y-m-d')) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('route_date') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            @error('route_date')
                                <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Pemilihan Multi-Toko Berurutan (FR-06, FR-07, TC-07, TC-10) -->
                    <div class="pt-4 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
                                    Urutan Toko yang Harus Dikunjungi <span class="text-rose-500">*</span>
                                </h3>
                                <p class="text-xs text-slate-400">Atur urutan titik singgah toko (urutan 1, 2, dst). Toko tidak boleh ganda.</p>
                            </div>
                            <button type="button" @click="addStop()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Tambah Titik Singgah
                            </button>
                        </div>

                        @error('stores')
                            <p class="text-xs text-rose-600 mb-3 font-medium">{{ $message }}</p>
                        @enderror

                        <div class="space-y-3">
                            <template x-for="(stop, index) in stops" :key="index">
                                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                                    <div class="w-7 h-7 rounded-lg bg-blue-600 text-white font-bold flex items-center justify-center text-xs shrink-0" x-text="index + 1">
                                    </div>
                                    <div class="flex-1">
                                        <select name="stores[]" required x-model="stop.id"
                                                class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                            <option value="">-- Pilih Toko Titik Singgah --</option>
                                            @foreach($activeStores as $store)
                                                <option value="{{ $store->id }}">
                                                    {{ $store->name }} - {{ $store->owner_name }} ({{ $store->address }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="button" @click="removeStop(index)" :disabled="stops.length <= 1"
                                            class="p-2 text-slate-400 hover:text-rose-600 disabled:opacity-30 disabled:hover:text-slate-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Catatan Agenda -->
                    <div>
                        <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Catatan atau Instruksi Rute
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                                  placeholder="Contoh: Fokus pada penawaran produk baru, pastikan toko pertama didatangi pagi hari..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="{{ route('rute.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-all">
                            Simpan &amp; Terbitkan Rute
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layout>
