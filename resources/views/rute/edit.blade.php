<x-layout title="Edit Rute Kunjungan">
    @php
        $hasCheckins = $route->visits->whereNotNull('checked_in_at')->isNotEmpty();
        $initialStops = old('stores') ?? $route->visits->sortBy('sequence')->pluck('store_id')->all();
    @endphp
    <div class="max-w-2xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <a href="{{ route('rute.detail', $route->id) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2">
                    &larr; Kembali ke Detail Rute
                </a>
                <h1 class="text-2xl font-bold text-slate-900">Edit Rute #{{ $route->id }}</h1>
                <p class="text-sm text-slate-500 mt-1">Ubah nama rute, tanggal jadwal, urutan toko, atau status penyelesaian.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <form action="{{ route('rute.update', $route->id) }}" method="POST" x-data="{
                stops: {{ Js::from(collect($initialStops)->map(fn ($sid) => ['id' => (string) $sid])->values()) }},
                addStop() { this.stops.push({ id: '' }); },
                removeStop(i) { if (this.stops.length > 1) this.stops.splice(i, 1); },
                move(i, d) {
                    const j = i + d;
                    if (j < 0 || j >= this.stops.length) return;
                    [this.stops[i], this.stops[j]] = [this.stops[j], this.stops[i]];
                }
            }">
                @csrf
                @method('PUT')

                <div class="space-y-5">
                    <!-- Nama Rute -->
                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Nama Rute Kunjungan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $route->name) }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('name') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @error('name')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Rute -->
                    <div>
                        <label for="route_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Tanggal Rencana Kunjungan <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="route_date" id="route_date" value="{{ old('route_date', $route->route_date->format('Y-m-d')) }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('route_date') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @error('route_date')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Rute -->
                    <div>
                        <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Status Rute <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" id="status" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="planned" {{ old('status', $route->status) == 'planned' ? 'selected' : '' }}>Direncanakan (planned)</option>
                            <option value="in_progress" {{ old('status', $route->status) == 'in_progress' ? 'selected' : '' }}>Sedang Berjalan (in_progress)</option>
                            <option value="completed" {{ old('status', $route->status) == 'completed' ? 'selected' : '' }}>Selesai (completed)</option>
                            <option value="cancelled" {{ old('status', $route->status) == 'cancelled' ? 'selected' : '' }}>Dibatalkan (cancelled)</option>
                        </select>
                        @error('status')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Daftar & Urutan Toko -->
                    <div class="pt-4 border-t border-slate-100">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Urutan Toko</h3>
                        @error('stores')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror

                        @if($hasCheckins)
                            <p class="text-xs text-amber-600 mt-1 mb-3">Daftar toko dikunci karena rute ini sudah memiliki check-in.</p>
                            <ol class="space-y-2">
                                @foreach($route->visits->sortBy('sequence') as $v)
                                    <li class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-sm">
                                        <span class="w-7 h-7 rounded-lg bg-slate-400 text-white font-bold flex items-center justify-center text-xs">{{ $v->sequence }}</span>
                                        <span class="text-slate-700">{{ $v->store->name ?? 'Toko dihapus' }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        @else
                            <div class="flex items-center justify-between mt-1 mb-3">
                                <p class="text-xs text-slate-400">Ubah, tambah, hapus, atau geser urutan toko selama belum ada check-in.</p>
                                <button type="button" @click="addStop()" class="px-3 py-1.5 text-xs font-semibold bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg">+ Tambah Toko</button>
                            </div>
                            <div class="space-y-3">
                                <template x-for="(stop, index) in stops" :key="index">
                                    <div class="flex items-center gap-2 p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                                        <div class="w-7 h-7 rounded-lg bg-blue-600 text-white font-bold flex items-center justify-center text-xs shrink-0" x-text="index + 1"></div>
                                        <select name="stores[]" required x-model="stop.id" class="flex-1 min-w-0 px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                            <option value="">-- Pilih Toko --</option>
                                            @foreach($activeStores as $store)
                                                <option value="{{ $store->id }}">{{ $store->name }} - {{ $store->owner_name }} ({{ $store->address }})</option>
                                            @endforeach
                                        </select>
                                        <button type="button" @click="move(index, -1)" :disabled="index === 0" class="p-2 text-slate-400 hover:text-blue-600 disabled:opacity-30" title="Naik">&uarr;</button>
                                        <button type="button" @click="move(index, 1)" :disabled="index === stops.length - 1" class="p-2 text-slate-400 hover:text-blue-600 disabled:opacity-30" title="Turun">&darr;</button>
                                        <button type="button" @click="removeStop(index)" :disabled="stops.length <= 1" class="p-2 text-slate-400 hover:text-rose-600 disabled:opacity-30" title="Hapus">&times;</button>
                                    </div>
                                </template>
                            </div>
                        @endif
                    </div>

                    <!-- Catatan Agenda -->
                    <div>
                        <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Catatan Rute
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">{{ old('notes', $route->notes) }}</textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="{{ route('rute.detail', $route->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-all">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layout>
