{{-- Memuat Leaflet (peta OpenStreetMap) satu kali per halaman. --}}
@once
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        <style>
            .rs-pin {
                display: flex; align-items: center; justify-content: center;
                border-radius: 9999px; color: #fff; font-weight: 800; font-size: 13px;
                border: 3px solid #fff; box-shadow: 0 2px 6px rgba(15, 23, 42, .35);
            }
            .rs-pin-active { box-shadow: 0 0 0 4px rgba(37, 99, 235, .35), 0 2px 8px rgba(15, 23, 42, .4); }
        </style>
    @endpush
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @endpush
@endonce
