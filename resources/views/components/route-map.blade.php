@props(['visits', 'highlight' => null, 'height' => '420px'])

@php
    $mapId = 'route-map-' . uniqid();

    $allStops = collect($visits)->map(fn ($v) => [
        'id'      => $v->id,
        'seq'     => $v->sequence,
        'name'    => $v->store->name ?? 'Toko',
        'address' => $v->store->address ?? '',
        'lat'     => $v->store?->latitude,
        'lng'     => $v->store?->longitude,
        'state'   => $v->result ?? 'pending',
    ])->values();

    $located = $allStops->filter(fn ($s) => !is_null($s['lat']) && !is_null($s['lng']))->values();
    $missing = $allStops->filter(fn ($s) => is_null($s['lat']) || is_null($s['lng']))->values();
@endphp

<x-leaflet-assets />

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-8">
    <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h2 class="font-bold text-slate-900 text-base">Peta Rute Kunjungan</h2>
            <p class="text-xs text-slate-400">Jalur mengikuti jalan, berurutan dari toko #1 sampai toko terakhir.</p>
        </div>
        <div id="{{ $mapId }}-info" class="text-xs font-semibold text-slate-600"></div>
    </div>

    @if($missing->isNotEmpty())
        <div class="px-5 py-3 bg-amber-50 border-b border-amber-100 text-xs text-amber-800">
            <strong>{{ $missing->count() }} toko belum punya koordinat</strong> sehingga tidak tampil di peta:
            {{ $missing->map(fn ($s) => '#' . $s['seq'] . ' ' . $s['name'])->implode(', ') }}.
            Lengkapi lokasinya lewat menu Edit Toko.
        </div>
    @endif

    @if($located->isEmpty())
        <div class="p-10 text-center text-sm text-slate-400">Belum ada toko dengan koordinat pada rute ini.</div>
    @else
        <div id="{{ $mapId }}" style="height: {{ $height }}; width: 100%; z-index: 0;"></div>
    @endif
</div>

@if($located->isNotEmpty())
    @push('scripts')
        <script>
            (function () {
                const stops = @json($located);
                const highlightId = @json($highlight);
                const el = document.getElementById(@json($mapId));
                const info = document.getElementById(@json($mapId . '-info'));
                const colors = { pending: '#2563eb', successful: '#059669', store_closed: '#d97706', failed: '#e11d48' };

                const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
                }[c]));

                const map = L.map(el, { scrollWheelZoom: false });
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(map);

                const latlngs = [];
                stops.forEach((s) => {
                    const active = highlightId !== null && s.id === highlightId;
                    const size = active ? 38 : 30;
                    const icon = L.divIcon({
                        className: '',
                        iconSize: [size, size],
                        iconAnchor: [size / 2, size / 2],
                        popupAnchor: [0, -size / 2],
                        html: `<div class="rs-pin ${active ? 'rs-pin-active' : ''}" style="width:${size}px;height:${size}px;background:${colors[s.state] || colors.pending}">${esc(s.seq)}</div>`
                    });
                    L.marker([s.lat, s.lng], { icon, zIndexOffset: active ? 1000 : 0 })
                        .addTo(map)
                        .bindPopup(`<strong>#${esc(s.seq)} ${esc(s.name)}</strong><br><span style="font-size:12px">${esc(s.address)}</span>`);
                    latlngs.push([s.lat, s.lng]);
                });

                map.fitBounds(L.latLngBounds(latlngs).pad(0.2), { maxZoom: 16 });
                if (latlngs.length < 2) return;

                // Jalur jalan via OSRM (server demo publik, gratis, tanpa API key).
                const coords = stops.map((s) => `${s.lng},${s.lat}`).join(';');
                const url = `https://router.project-osrm.org/route/v1/driving/${coords}?overview=full&geometries=geojson`;

                fetch(url)
                    .then((r) => r.json())
                    .then((data) => {
                        if (data.code !== 'Ok' || !data.routes.length) throw new Error(data.code || 'no route');
                        const route = data.routes[0];
                        const line = L.geoJSON(route.geometry, { style: { color: '#2563eb', weight: 5, opacity: 0.85 } }).addTo(map);
                        map.fitBounds(line.getBounds().pad(0.15));
                        const km = (route.distance / 1000).toFixed(1);
                        const min = Math.round(route.duration / 60);
                        info.textContent = `±${km} km • ±${min} menit berkendara (tanpa macet)`;
                    })
                    .catch(() => {
                        // Cadangan: garis lurus putus-putus antar toko jika layanan rute tidak tersedia.
                        L.polyline(latlngs, { color: '#94a3b8', weight: 3, dashArray: '8 8' }).addTo(map);
                        info.textContent = 'Jalur jalan tidak tersedia. Menampilkan garis lurus antar toko.';
                    });
            })();
        </script>
    @endpush
@endif
