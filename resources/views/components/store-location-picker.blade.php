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
        Isi alamat lalu klik tombol di atas. Geser pin atau klik peta untuk mengoreksi posisi.
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
        (function () {
            const addrEl = document.getElementById('address');
            const areaEl = document.getElementById('area');
            const latEl = document.getElementById('latitude');
            const lngEl = document.getElementById('longitude');
            const btn = document.getElementById('geocode-btn');
            const statusEl = document.getElementById('geocode-status');

            const num = (v) => { const n = parseFloat(v); return Number.isFinite(n) ? n : null; };
            const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

            // Penanda: jika admin sudah mengoreksi manual, pencarian otomatis tidak menimpanya.
            let manual = false;
            let busy = false;
            let marker = null;

            const startLat = num(latEl.value), startLng = num(lngEl.value);
            const hasStart = startLat !== null && startLng !== null;

            const map = L.map('store-map', { scrollWheelZoom: false })
                .setView(hasStart ? [startLat, startLng] : [-7.3, 110.5], hasStart ? 16 : 7);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

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

            // Kandidat kueri: dari paling spesifik ke paling umum (alamat informal sering gagal ditemukan).
            function buildQueries() {
                const area = (areaEl?.value || '').trim();
                const parts = addrEl.value.split(',').map((p) => p.trim()).filter(Boolean);
                const list = [];
                if (parts.length) {
                    list.push([parts.join(', '), area].filter(Boolean).join(', '));
                    list.push(parts.join(', '));
                    for (let i = 1; i <= Math.min(2, parts.length - 1); i++) {
                        list.push([parts.slice(i).join(', '), area].filter(Boolean).join(', '));
                    }
                    if (area) list.push(area);
                }
                return [...new Set(list.filter(Boolean))];
            }

            async function geocode() {
                if (busy) return;
                if (!addrEl.value.trim()) { setStatus('Isi alamat toko terlebih dahulu.', 'warn'); return; }
                busy = true; btn.disabled = true; btn.textContent = 'Mencari...';
                setStatus('Mencari lokasi...', 'info');

                try {
                    const queries = buildQueries();
                    for (let i = 0; i < queries.length; i++) {
                        if (i > 0) await sleep(1100); // batas Nominatim: 1 permintaan/detik
                        const url = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&countrycodes=id&accept-language=id&q=' + encodeURIComponent(queries[i]);
                        const res = await fetch(url);
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        const data = await res.json();
                        if (data.length) {
                            manual = false;
                            setPoint(parseFloat(data[0].lat), parseFloat(data[0].lon), i === 0 ? 17 : 15);
                            setStatus(i === 0
                                ? 'Lokasi ditemukan. Periksa pin, geser jika kurang tepat.'
                                : 'Alamat persis tidak ditemukan. Pin hanya perkiraan area, geser ke lokasi toko yang benar.',
                                i === 0 ? 'ok' : 'warn');
                            return;
                        }
                    }
                    setStatus('Alamat tidak ditemukan. Klik langsung di peta untuk menandai lokasi toko.', 'err');
                } catch (e) {
                    setStatus('Layanan pencarian alamat sedang tidak bisa dihubungi. Klik langsung di peta untuk menandai lokasi.', 'err');
                } finally {
                    busy = false; btn.disabled = false; btn.textContent = 'Cari Lokasi dari Alamat';
                }
            }

            btn.addEventListener('click', geocode);

            // Otomatis: saat keluar dari kolom alamat, jika koordinat masih kosong dan belum dikoreksi manual.
            addrEl.addEventListener('blur', () => {
                if (!manual && latEl.value.trim() === '' && addrEl.value.trim() !== '') geocode();
            });
            // Alamat diubah saat koordinat sudah ada: ingatkan agar diperbarui.
            addrEl.addEventListener('input', () => {
                if (latEl.value.trim() !== '' && !busy) {
                    setStatus('Alamat berubah. Klik "Cari Lokasi dari Alamat" jika posisi pin perlu diperbarui.', 'warn');
                }
            });
        })();
    </script>
@endpush
