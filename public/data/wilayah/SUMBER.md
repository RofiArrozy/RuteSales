# Sumber data wilayah

Berkas `provinces.json` dan `{kodeProvinsi}.json` di folder ini adalah hasil konversi data wilayah administratif
Indonesia (38 provinsi, 514 kabupaten/kota, 7.285 kecamatan, 83.762 kelurahan/desa, beserta kode pos dan titik pusat
koordinat) dari repositori **emsifa/api-wilayah-indonesia** (lisensi MIT), cabang `gh-pages`, folder `v2/`.
Repositori itu sendiri merangkum data dari **cahyadsn/wilayah**, **cahyadsn/wilayah_kodepos**, dan
**cahyadsn/wilayah_boundaries** (juga MIT). Rujukan hierarki: Kepmendagri; titik pusat kecamatan/kelurahan dari BIG.

Format ringkas (satu berkas per provinsi):

```
{ id, name, lat, lng,
  regencies: [ { id, name, lat, lng,
    districts: [ { id, name, lat, lng,
      villages: [ [kodeDesa, nama, kodePos, lat, lng], ... ] } ] } ] }
```

Koordinat adalah titik pusat wilayah (bukan lokasi toko) dan dibulatkan 5 desimal. Data dapat berubah (pemekaran, perubahan
kode pos); perbarui dengan mengonversi ulang dari sumber di atas.
