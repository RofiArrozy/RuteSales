/*
 * Alamat terstruktur toko: dropdown wilayah (provinsi > kab/kota > kecamatan > kelurahan) + pencarian koordinat bertahap.
 * Fungsi murni di bagian atas diekspor untuk pengujian dengan Node (tests/js/address.test.js).
 */
(function (root) {
    'use strict';

    var BASE = '/data/wilayah';

    // ---------- Fungsi murni ----------

    /** Bersihkan nama jalan dari bagian yang membuat pencarian peta gagal (nomor, RT/RW, dusun, blok). */
    function simplifyStreet(street) {
        var t = ' ' + String(street || '') + ' ';
        t = t
            .replace(/\b(no|nomor)\.?\s*\d+[a-z]?(\s*[-\/]\s*\d+[a-z]?)?/gi, ' ')
            .replace(/\brt\.?\s*\d+(\s*\/?\s*rw\.?\s*\d+)?/gi, ' ')
            .replace(/\brw\.?\s*\d+/gi, ' ')
            .replace(/\b(dusun|dsn|dukuh|blok)\b\.?\s*[^\s,;]+/gi, ' ')
            .replace(/\bjl\.?(\s|$)/gi, 'Jalan ')
            .replace(/[,;]+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
        return t;
    }

    /** Buang segmen (dipisah koma) yang hanya berisi nama wilayah terpilih atau kode pos. */
    function stripRegion(street, parts) {
        var norm = function (s) {
            return String(s || '').toLowerCase().trim()
                .replace(/\s*\b\d{5}$/, '')
                .replace(/^(kecamatan|kec|kelurahan|kel|desa|kabupaten|kab|kota|provinsi|prov)\.?\s+/, '')
                .replace(/\s+/g, ' ').trim();
        };
        var known = [parts.village, parts.district, parts.regency, parts.province].map(norm).filter(Boolean);
        return String(street || '').split(/\s*,\s*/).filter(function (seg) {
            var n = norm(seg);
            return n !== '' && known.indexOf(n) === -1 && !/^\d{5}$/.test(seg.trim());
        }).join(', ');
    }

    /** Daftar kueri dari paling spesifik ke paling umum. Kosong jika jalan belum diisi. */
    function buildGeocodeQueries(parts) {
        var street = simplifyStreet(stripRegion(parts.street, parts));
        if (!street) return [];
        var list = [];
        var add = function (arr) {
            var q = arr.filter(Boolean).join(', ');
            if (q && list.indexOf(q) === -1) list.push(q);
        };
        add([street, parts.village, parts.district, parts.regency]);
        add([street, parts.district, parts.regency]);
        add([street, parts.regency]);
        return list;
    }

    function haversineKm(a, b) {
        var R = 6371, rad = Math.PI / 180;
        var dLat = (b.lat - a.lat) * rad, dLng = (b.lng - a.lng) * rad;
        var h = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(a.lat * rad) * Math.cos(b.lat * rad) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
        return 2 * R * Math.asin(Math.sqrt(h));
    }

    /** Ambil hasil pertama yang berada dalam maxKm dari titik pusat wilayah terpilih (membuang kecocokan salah kota). */
    function pickResult(results, center, maxKm) {
        for (var i = 0; i < (results || []).length; i++) {
            var p = { lat: parseFloat(results[i].lat), lng: parseFloat(results[i].lon) };
            if (!isFinite(p.lat) || !isFinite(p.lng)) continue;
            if (!center || haversineKm(center, p) <= maxKm) return p;
        }
        return null;
    }

    function nominatimUrl(query, center, boxDeg) {
        var url = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=5&countrycodes=id&accept-language=id&q=' + encodeURIComponent(query);
        if (center) {
            var l = center.lng - boxDeg, r = center.lng + boxDeg, t = center.lat + boxDeg, b = center.lat - boxDeg;
            url += '&bounded=1&viewbox=' + [l, t, r, b].map(function (n) { return n.toFixed(5); }).join(',');
        }
        return url;
    }

    /**
     * Cari koordinat alamat secara bertahap. fetchFn dan sleep disuntikkan agar bisa diuji tanpa jaringan.
     * Mengembalikan { lat, lng, query, step } atau null bila tidak ada yang cocok.
     */
    async function geocodeAddress(opts) {
        var queries = buildGeocodeQueries(opts.parts);
        var maxKm = opts.maxKm || 8, boxDeg = opts.boxDeg || 0.06;
        for (var i = 0; i < queries.length; i++) {
            if (i > 0) await opts.sleep(1100); // batas Nominatim: 1 permintaan/detik
            var res = await opts.fetchFn(nominatimUrl(queries[i], opts.center, boxDeg));
            if (!res.ok) throw new Error('HTTP ' + res.status);
            var hit = pickResult(await res.json(), opts.center, maxKm);
            if (hit) return { lat: hit.lat, lng: hit.lng, query: queries[i], step: i };
        }
        return null;
    }

    var api = {
        simplifyStreet: simplifyStreet,
        stripRegion: stripRegion,
        buildGeocodeQueries: buildGeocodeQueries,
        haversineKm: haversineKm,
        pickResult: pickResult,
        nominatimUrl: nominatimUrl,
        geocodeAddress: geocodeAddress
    };

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = api;
    }
    root.RuteSalesAddress = api;

    // ---------- Komponen Alpine (hanya di browser) ----------

    if (typeof document === 'undefined') return;

    var fileCache = {};
    function loadJson(path) {
        if (!fileCache[path]) {
            fileCache[path] = fetch(path).then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            });
            fileCache[path].catch(function () { delete fileCache[path]; });
        }
        return fileCache[path];
    }

    document.addEventListener('alpine:init', function () {
        window.Alpine.data('addressPicker', function (cfg) {
            return {
                provinces: [], regencies: [], districts: [], villages: [],
                province: cfg.province || '', regency: cfg.regency || '', district: cfg.district || '', village: cfg.village || '',
                street: cfg.street || '',
                postal: cfg.postal || '',
                provinceData: null,
                loadError: '',

                async init() {
                    try {
                        this.provinces = await loadJson(BASE + '/provinces.json');
                        if (this.province) {
                            await this.loadProvince(this.province);
                            this.syncLevels();
                        }
                    } catch (e) {
                        this.loadError = 'Data wilayah gagal dimuat. Muat ulang halaman.';
                    }
                    this.emit('init');
                },

                async loadProvince(code) {
                    this.provinceData = await loadJson(BASE + '/' + code + '.json');
                    this.regencies = this.provinceData.regencies;
                },

                syncLevels() {
                    var r = this.regencies.find(function (x) { return x.id === this.regency; }.bind(this));
                    this.districts = r ? r.districts : [];
                    var d = this.districts.find(function (x) { return x.id === this.district; }.bind(this));
                    this.villages = d ? d.villages : [];
                },

                async onProvince(code) {
                    this.province = code; this.regency = ''; this.district = ''; this.village = '';
                    this.regencies = []; this.districts = []; this.villages = []; this.postal = ''; this.loadError = '';
                    if (code) {
                        try { await this.loadProvince(code); } catch (e) { this.loadError = 'Data wilayah gagal dimuat.'; }
                    }
                    this.emit('province');
                },
                onRegency(code) {
                    this.regency = code; this.district = ''; this.village = ''; this.postal = '';
                    this.syncLevels(); this.emit('regency');
                },
                onDistrict(code) {
                    this.district = code; this.village = ''; this.postal = '';
                    this.syncLevels(); this.emit('district');
                },
                onVillage(code) {
                    this.village = code;
                    var v = this.villages.find(function (x) { return x[0] === code; });
                    this.postal = v && v[2] ? v[2] : '';
                    this.emit('village');
                },

                /** Nama & titik pusat wilayah terdalam yang sudah dipilih. */
                current() {
                    var p = this.provinces.find(function (x) { return x.id === this.province; }.bind(this));
                    var r = this.regencies.find(function (x) { return x.id === this.regency; }.bind(this));
                    var d = this.districts.find(function (x) { return x.id === this.district; }.bind(this));
                    var v = this.villages.find(function (x) { return x[0] === this.village; }.bind(this));
                    var center = null, level = null;
                    if (v && v[3] != null) { center = { lat: v[3], lng: v[4] }; level = 'village'; }
                    else if (d && d.lat != null) { center = { lat: d.lat, lng: d.lng }; level = 'district'; }
                    else if (r && r.lat != null) { center = { lat: r.lat, lng: r.lng }; level = 'regency'; }
                    else if (p && p.lat != null) { center = { lat: p.lat, lng: p.lng }; level = 'province'; }
                    return {
                        parts: {
                            street: this.street,
                            village: v ? v[1] : '', district: d ? d.name : '',
                            regency: r ? r.name : '', province: p ? p.name : ''
                        },
                        center: center, level: level
                    };
                },

                emit(source) {
                    var state = this.current();
                    root.RuteSalesAddress.state = state;
                    window.dispatchEvent(new CustomEvent('rs-address-change', { detail: { source: source, state: state } }));
                }
            };
        });
    });
})(typeof window !== 'undefined' ? window : globalThis);
