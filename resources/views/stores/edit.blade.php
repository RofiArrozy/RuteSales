<x-layout title="Edit Data Toko">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <a href="{{ route('stores.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2">
                    &larr; Kembali ke Daftar Toko
                </a>
                <h1 class="text-2xl font-bold text-slate-900">Edit Toko: {{ $store->name }}</h1>
                <p class="text-sm text-slate-500 mt-1">Perbarui data profil dan koordinat lokasi toko/warung.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <form action="{{ route('stores.update', $store->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Nama Toko -->
                        <div>
                            <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Nama Toko / Warung <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $store->name) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('name') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            @error('name')
                                <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nama Pemilik -->
                        <div>
                            <label for="owner_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Nama Pemilik <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="owner_name" id="owner_name" value="{{ old('owner_name', $store->owner_name) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('owner_name') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            @error('owner_name')
                                <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- No. Telepon -->
                        <div>
                            <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                No. Telepon / WhatsApp
                            </label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $store->phone) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            @error('phone')
                                <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Wilayah / Area -->
                        <div>
                            <label for="area" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Wilayah / Cluster Area
                            </label>
                            <input type="text" name="area" id="area" value="{{ old('area', $store->area) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            @error('area')
                                <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Alamat terstruktur (dropdown wilayah + jalan) -->
                    <x-address-fields :store="$store" />

                    <!-- Lokasi Toko di Peta (geocoding dari alamat + pin yang bisa digeser) -->
                    <x-store-location-picker :lat="old('latitude', $store->latitude)" :lng="old('longitude', $store->longitude)" />

                    <!-- Status Toko -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Status Outlet <span class="text-rose-500">*</span>
                            </label>
                            <select name="status" id="status" required
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                <option value="active" {{ old('status', $store->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ old('status', $store->status) == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                            </select>
                            @error('status')
                                <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Catatan Tambahan -->
                        <div>
                            <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Catatan Khusus
                            </label>
                            <input type="text" name="notes" id="notes" value="{{ old('notes', $store->notes) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="{{ route('stores.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
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

