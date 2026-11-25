@props(['store' => null])

@php
    $val = fn ($k) => old($k, $store?->{$k});
    $cfg = [
        'province' => (string) $val('province_code'),
        'regency' => (string) $val('regency_code'),
        'district' => (string) $val('district_code'),
        'village' => (string) $val('village_code'),
        'street' => (string) $val('street'),
        'postal' => (string) ($store?->postal_code ?? ''),
    ];
    $legacy = $store && !$store->hasStructuredAddress() && filled($store->address);
    $sel = 'w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 disabled:bg-slate-50 disabled:text-slate-400';
@endphp

<div x-data='addressPicker(@json($cfg))' class="space-y-4" id="address-fields">
    <div>
        <p class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
            Alamat Toko <span class="text-rose-500">*</span>
        </p>
        <p class="text-xs text-slate-500 mt-1">Pilih wilayah dari atas ke bawah, lalu tulis nama jalan. Peta akan langsung menuju wilayah yang dipilih.</p>
    </div>

    @if ($legacy)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2.5 text-xs text-amber-800">
            <p class="font-semibold">Alamat lama (teks bebas), mungkin kurang akurat:</p>
            <p class="mt-0.5">{{ $store->address }}</p>
            <p class="mt-1">Isi ulang dengan pilihan di bawah agar lokasi di peta akurat. Jika tidak diubah, alamat lama tetap dipakai.</p>
        </div>
    @endif

    <p x-show="loadError" x-text="loadError" class="text-xs text-rose-600 font-medium" x-cloak></p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="province_code" class="block text-xs text-slate-500 mb-1">Provinsi</label>
            <select name="province_code" id="province_code" class="{{ $sel }}" @change="onProvince($event.target.value)">
                <option value="">Pilih provinsi</option>
                <template x-for="p in provinces" :key="p.id">
                    <option :value="p.id" x-text="p.name" :selected="p.id === province"></option>
                </template>
            </select>
        </div>
        <div>
            <label for="regency_code" class="block text-xs text-slate-500 mb-1">Kabupaten / Kota</label>
            <select name="regency_code" id="regency_code" class="{{ $sel }}" :disabled="!province" @change="onRegency($event.target.value)">
                <option value="">Pilih kabupaten / kota</option>
                <template x-for="r in regencies" :key="r.id">
                    <option :value="r.id" x-text="r.name" :selected="r.id === regency"></option>
                </template>
            </select>
        </div>
        <div>
            <label for="district_code" class="block text-xs text-slate-500 mb-1">Kecamatan</label>
            <select name="district_code" id="district_code" class="{{ $sel }}" :disabled="!regency" @change="onDistrict($event.target.value)">
                <option value="">Pilih kecamatan</option>
                <template x-for="d in districts" :key="d.id">
                    <option :value="d.id" x-text="d.name" :selected="d.id === district"></option>
                </template>
            </select>
        </div>
        <div>
            <label for="village_code" class="block text-xs text-slate-500 mb-1">Kelurahan / Desa</label>
            <select name="village_code" id="village_code" class="{{ $sel }}" :disabled="!district" @change="onVillage($event.target.value)">
                <option value="">Pilih kelurahan / desa</option>
                <template x-for="v in villages" :key="v[0]">
                    <option :value="v[0]" x-text="v[1]" :selected="v[0] === village"></option>
                </template>
            </select>
        </div>
    </div>

    <div>
        <label for="street" class="block text-xs text-slate-500 mb-1">Nama jalan, nomor, RT/RW, patokan</label>
        <textarea name="street" id="street" rows="2" x-model="street" @blur="emit('street-blur')"
                  placeholder="Contoh: Jl. Ahmad Yani No. 12, RT 02/RW 05, depan Alfamart"
                  class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('address') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"></textarea>
        <p class="text-xs text-slate-500 mt-1" x-show="postal" x-cloak>Kode pos: <span class="font-semibold" x-text="postal"></span></p>
        @error('address')
            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
        @enderror
        @error('village_code')
            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
        @enderror
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/rutesales-address.js') }}"></script>
@endpush
