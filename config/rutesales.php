<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Verifikasi Lokasi Check-in
    |--------------------------------------------------------------------------
    | Check-in ditandai "jauh dari toko" jika jarak (setelah dikurangi akurasi GPS)
    | melebihi radius ini. Pembacaan GPS dengan akurasi lebih buruk dari batas
    | akurasi maksimum dianggap "tidak terverifikasi".
    */
    'gps_radius_m' => (int) env('RUTESALES_GPS_RADIUS_M', 200),
    'gps_max_accuracy_m' => (int) env('RUTESALES_GPS_MAX_ACCURACY_M', 500),

];
