@props(['lat' => null, 'lng' => null])

<x-leaflet-assets />

<div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
        <p class="text-xs font-bold text-slate-800">Lokasi Toko di Peta</p>
        <button type="button" id="geocode-btn"
                class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-all">
            Cari Lokasi dari Alamat
        </button>
    </div>

    <p id="geocode-status" class="text-xs text-slate-500 mb-3">
        Pilih wilayah dan tulis nama jalan; peta menuju wilayah itu otomatis. Klik tombol di atas untuk mencari titik jalannya, atau geser pin / klik peta untuk mengoreksi.
    </p>

    <div id="store-map" class="rounded-xl border border-slate-200" style="height: 320px; width: 100%; z-index: 0;"></div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
        <div>
            <label for="latitude" class="block text-xs text-slate-500 mb-1">Latitude</label>
            <input type="text" name="latitude" id="latitude" value="{{ $lat }}" placeholder="Terisi otomatis"
                   class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            @error('latitude')
                <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="longitude" class="block text-xs text-slate-500 mb-1">Longitude</label>
            <input type="text" name="longitude" id="longitude" value="{{ $lng }}" placeholder="Terisi otomatis"
                   class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            @error('longitude')
                <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Leaflet dimuat oleh stack yang sama; tunggu halaman siap agar urutan <script> tidak berpengaruh.
        document.addEventListener('DOMContentLoaded', function () {
            const latEl = document.getElementById('latitude');
            const lngEl = document.getElementById('longitude');
            const btn = document.getElementById('geocode-btn');
            const statusEl = document.getElementById('geocode-status');

            const num = (v) => { const n = parseFloat(v); return Number.isFinite(n) ? n : null; };
            const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
            const ZOOM = { village: 16, district: 14, regency: 12, province: 8 };

            // Jika admin sudah menggeser pin / mengisi koordinat sendiri, pencarian otomatis tidak menimpanya.
            let manual = false;
            let busy = false;
            let marker = null;

            const startLat = num(latEl.value), startLng = num(lngEl.value);
            const hasStart = startLat !== null && startLng !== null;
            // Koordinat tersimpan/lama dianggap sudah dipilih admin: jangan ditimpa saat dropdown dimuat.
            if (hasStart) manual = true;

            const map = L.map('store-map', { scrollWheelZoom: false })
                .setView(hasStart ? [startLat, startLng] : [-2.5, 118], hasStart ? 16 : 5);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);
            window.rsStoreMap = map;

            function writeInputs(lat, lng) {
                latEl.value = lat.toFixed(7);
                lngEl.value = lng.toFixed(7);
            }

            function setPoint(lat, lng, zoom) {
                if (!marker) {
                    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                    marker.on('dragend', () => {
                        const p = marker.getLatLng();
                        manual = true;
                        writeInputs(p.lat, p.lng);
                        setStatus('Posisi dikoreksi manual.', 'ok');
                    });
                } else {
                    marker.setLatLng([lat, lng]);
                }
                writeInputs(lat, lng);
                if (zoom) map.setView([lat, lng], zoom);
            }

            function setStatus(text, kind) {
                statusEl.textContent = text;
                statusEl.className = 'text-xs mb-3 ' + (kind === 'ok' ? 'text-emerald-700' : kind === 'warn' ? 'text-amber-700' : kind === 'err' ? 'text-rose-600' : 'text-slate-500');
            }

            if (hasStart) setPoint(startLat, startLng);

            map.on('click', (e) => {
                manual = true;
                setPoint(e.latlng.lat, e.latlng.lng);
                setStatus('Posisi dipilih manual dari peta.', 'ok');
            });

            [latEl, lngEl].forEach((input) => input.addEventListener('change', () => {
                const la = num(latEl.value), ln = num(lngEl.value);
                if (la !== null && ln !== null) {
                    manual = true;
                    setPoint(la, ln, 16);
                }
            }));

            const state = () => (window.RuteSalesAddress && window.RuteSalesAddress.state) || null;

            async function geocode(auto) {
                if (busy) return;
                const st = state();
                if (!st || !st.parts.village) { setStatus('Pilih provinsi sampai kelurahan/desa terlebih dahulu.', 'warn'); return; }
                if (!st.parts.street.trim()) { setStatus('Tulis nama jalan terlebih dahulu.', 'warn'); return; }

                busy = true; btn.disabled = true; btn.textContent = 'Mencari...';
                setStatus('Mencari lokasi jalan...', 'info');
                try {
                    const hit = await window.RuteSalesAddress.geocodeAddress({
                        parts: st.parts, center: st.center, fetchFn: (u) => fetch(u), sleep: sleep
                    });
                    if (hit) {
                        manual = false;
                        setPoint(hit.lat, hit.lng, hit.step === 0 ? 17 : 15);
                        setStatus(hit.step === 0
                            ? 'Lokasi jalan ditemukan. Periksa pin, geser jika kurang tepat.'
                            : 'Nomor/jalan persisnya tidak ada di peta. Pin hanya perkiraan sekitar jalan itu, geser ke lokasi toko.',
                            hit.step === 0 ? 'ok' : 'warn');
                    } else {
                        setStatus('Jalan tidak ditemukan di peta. Pin tetap di pusat kelurahan; geser ke lokasi toko yang benar.', 'warn');
                    }
                } catch (e) {
                    setStatus('Layanan pencarian jalan sedang tidak bisa dihubungi. Pin tetap di pusat kelurahan; geser atau klik peta untuk menandai lokasi.', 'err');
                } finally {
                    busy = false; btn.disabled = false; btn.textContent = 'Cari Lokasi dari Alamat';
                }
            }

            btn.addEventListener('click', () => geocode(false));

            window.addEventListener('rs-address-change', (e) => {
                const { source, state: st } = e.detail;
                if (source === 'init' || !st.center) return;

                if (source === 'street-blur') {
                    if (!manual && st.parts.village && st.parts.street.trim()) geocode(true);
                    return;
                }
                // Pilihan wilayah berubah: peta langsung menuju pusat wilayah terdalam.
                if (manual) {
                    map.setView([st.center.lat, st.center.lng], ZOOM[st.level]);
                    setStatus('Wilayah berubah, tetapi pin manual tidak diubah. Klik "Cari Lokasi dari Alamat" bila perlu memperbarui.', 'warn');
                    return;
                }
                setPoint(st.center.lat, st.center.lng, ZOOM[st.level]);
                setStatus(st.level === 'village'
                    ? 'Pin di pusat kelurahan/desa (perkiraan). Tulis nama jalan lalu cari lokasi untuk lebih tepat.'
                    : 'Peta menuju wilayah terpilih. Lanjutkan sampai kelurahan/desa.', 'info');
            });
        });
    </script>
@endpush
