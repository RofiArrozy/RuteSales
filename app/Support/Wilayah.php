<?php

namespace App\Support;

/**
 * Data wilayah administratif Indonesia (provinsi, kabupaten/kota, kecamatan, kelurahan/desa)
 * dari berkas statis public/data/wilayah/{kodeProvinsi}.json (lihat public/data/wilayah/SUMBER.md).
 * Dipakai server untuk memastikan kombinasi wilayah yang dikirim form benar-benar konsisten.
 */
class Wilayah
{
    /** @var array<string, array|null> */
    private static array $cache = [];

    /**
     * Muat satu provinsi beserta seluruh turunannya, atau null jika kode tidak dikenal.
     */
    public static function province(string $code): ?array
    {
        if (!preg_match('/^\d{2}$/', $code)) {
            return null;
        }

        if (!array_key_exists($code, self::$cache)) {
            $path = public_path("data/wilayah/{$code}.json");
            $data = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
            self::$cache[$code] = is_array($data) ? $data : null;
        }

        return self::$cache[$code];
    }

    /**
     * Cari wilayah berdasarkan kode lengkap. Mengembalikan null bila ada kode yang tidak ditemukan
     * atau tidak berada di bawah induknya (mis. kelurahan yang bukan bagian dari kecamatan tersebut).
     *
     * @return array{province: array, regency: array, district: array, village: array}|null
     */
    public static function resolve(string $provinceCode, string $regencyCode, string $districtCode, string $villageCode): ?array
    {
        $province = self::province($provinceCode);
        if (!$province) {
            return null;
        }

        $regency = collect($province['regencies'] ?? [])->firstWhere('id', $regencyCode);
        $district = $regency ? collect($regency['districts'] ?? [])->firstWhere('id', $districtCode) : null;
        $villageRow = $district
            ? collect($district['villages'] ?? [])->first(fn ($v) => $v[0] === $villageCode)
            : null;

        if (!$regency || !$district || !$villageRow) {
            return null;
        }

        return [
            'province' => ['id' => $province['id'], 'name' => $province['name'], 'lat' => $province['lat'], 'lng' => $province['lng']],
            'regency' => ['id' => $regency['id'], 'name' => $regency['name'], 'lat' => $regency['lat'], 'lng' => $regency['lng']],
            'district' => ['id' => $district['id'], 'name' => $district['name'], 'lat' => $district['lat'], 'lng' => $district['lng']],
            'village' => [
                'id' => $villageRow[0],
                'name' => $villageRow[1],
                'postal_code' => $villageRow[2],
                'lat' => $villageRow[3],
                'lng' => $villageRow[4],
            ],
        ];
    }

    /**
     * Bersihkan kolom jalan dari bagian wilayah yang sudah dipilih lewat dropdown (kelurahan, kecamatan,
     * kabupaten/kota, provinsi, kode pos), mis. saat admin menempel alamat lengkap ke kolom jalan.
     * Hanya segmen terpisah koma yang dibuang, jadi "Jalan Pajang Raya" tidak ikut terhapus.
     */
    public static function cleanStreet(string $street, array $region): string
    {
        $norm = function (string $s): string {
            $s = mb_strtolower(trim($s));
            $s = preg_replace('/\s*\b\d{5}\b$/u', '', $s);
            $s = preg_replace('/^(kecamatan|kec|kelurahan|kel|desa|kabupaten|kab|kota|provinsi|prov)\.?\s+/u', '', $s);
            return trim(preg_replace('/\s+/u', ' ', $s));
        };

        $known = array_filter([
            $norm($region['village']['name']),
            $norm($region['district']['name']),
            $norm($region['regency']['name']),
            $norm($region['province']['name']),
        ]);
        $postal = (string) ($region['village']['postal_code'] ?? '');

        $kept = [];
        foreach (preg_split('/\s*,\s*/u', trim($street)) as $segment) {
            $n = $norm($segment);
            if ($n === '' || in_array($n, $known, true) || ($postal !== '' && trim($segment) === $postal)) {
                continue;
            }
            $kept[] = trim($segment);
        }

        return implode(', ', $kept);
    }

    /**
     * Susun alamat satu baris dari jalan + wilayah hasil resolve().
     */
    public static function compose(string $street, array $region): string
    {
        $parts = array_filter([
            trim($street),
            $region['village']['name'],
            'Kec. ' . $region['district']['name'],
            $region['regency']['name'],
            $region['province']['name'],
        ]);

        $address = implode(', ', $parts);
        if (!empty($region['village']['postal_code'])) {
            $address .= ' ' . $region['village']['postal_code'];
        }

        return $address;
    }
}
