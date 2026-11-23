{{-- Membaca lokasi perangkat sales saat check-in. Hasilnya hanya menjadi penanda untuk admin, tidak memblokir check-in. --}}
<div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Verifikasi Lokasi</h3>
            <p id="gps-status" class="text-xs text-slate-500 mt-1">Mendeteksi lokasi perangkat...</p>
        </div>
        <button type="button" id="gps-retry"
                class="shrink-0 px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 rounded-lg text-xs font-semibold transition-all">
            Ambil Ulang
        </button>
    </div>
    <input type="hidden" name="simulated_latitude" id="gps-lat">
    <input type="hidden" name="simulated_longitude" id="gps-lng">
    <input type="hidden" name="gps_accuracy" id="gps-acc">
</div>

@push('scripts')
    <script>
        (function () {
            const statusEl = document.getElementById('gps-status');
            const retryBtn = document.getElementById('gps-retry');
            const latEl = document.getElementById('gps-lat');
            const lngEl = document.getElementById('gps-lng');
            const accEl = document.getElementById('gps-acc');
            const form = statusEl.closest('form');
            const submitBtn = form ? form.querySelector('button[type="submit"]') : null;

            function setStatus(text, kind) {
                statusEl.textContent = text;
                statusEl.className = 'text-xs mt-1 ' + (kind === 'ok' ? 'text-emerald-700' : kind === 'warn' ? 'text-amber-700' : 'text-slate-500');
            }

            // Tombol simpan ditahan sebentar selama lokasi dibaca (kecuali sudah dikunci server).
            function holdSubmit(hold) {
                if (!submitBtn || submitBtn.dataset.locked) return;
                submitBtn.disabled = hold;
            }

            function clearFields() { latEl.value = ''; lngEl.value = ''; accEl.value = ''; }

            function locate() {
                clearFields();
                if (!('geolocation' in navigator)) {
                    setStatus('Browser tidak mendukung lokasi. Check-in tetap bisa, tetapi akan ditandai "lokasi tidak terverifikasi".', 'warn');
                    return;
                }
                if (!window.isSecureContext) {
                    setStatus('Lokasi hanya bisa dibaca lewat koneksi aman (HTTPS). Check-in tetap bisa, tetapi akan ditandai "lokasi tidak terverifikasi".', 'warn');
                    return;
                }
                setStatus('Mendeteksi lokasi perangkat...', 'info');
                holdSubmit(true);
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        latEl.value = pos.coords.latitude.toFixed(7);
                        lngEl.value = pos.coords.longitude.toFixed(7);
                        accEl.value = Math.round(pos.coords.accuracy);
                        setStatus('Lokasi terdeteksi (akurasi ±' + Math.round(pos.coords.accuracy) + ' m).', 'ok');
                        holdSubmit(false);
                    },
                    (err) => {
                        const msg = err.code === 1
                            ? 'Izin lokasi ditolak. Aktifkan izin lokasi lalu klik "Ambil Ulang".'
                            : 'Lokasi tidak berhasil dibaca. Coba "Ambil Ulang".';
                        setStatus(msg + ' Check-in tetap bisa, tetapi akan ditandai "lokasi tidak terverifikasi".', 'warn');
                        holdSubmit(false);
                    },
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                );
            }

            retryBtn.addEventListener('click', locate);
            locate();
        })();
    </script>
@endpush
