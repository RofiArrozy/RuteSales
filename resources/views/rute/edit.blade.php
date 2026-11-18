<x-layout title="Edit Rute Kunjungan">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <a href="{{ route('rute.detail', $route->id) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2">
                    &larr; Kembali ke Detail Rute
                </a>
                <h1 class="text-2xl font-bold text-slate-900">Edit Rute #{{ $route->id }}</h1>
                <p class="text-sm text-slate-500 mt-1">Ubah nama rute, tanggal jadwal, atau status penyelesaian.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <form action="{{ route('rute.update', $route->id) }}" method="POST">
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
