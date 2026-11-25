import test from 'node:test';
import assert from 'node:assert/strict';
import '../../public/js/rutesales-address.js';

const A = globalThis.RuteSalesAddress;
const parts = { street: 'Jl. Papagan No.13, Dusun II, RT 02/RW 05', village: 'Makamhaji', district: 'Kartasura', regency: 'Kabupaten Sukoharjo', province: 'Jawa Tengah' };
const center = { lat: -7.56628, lng: 110.77713 };
const resp = (data, ok = true, status = 200) => ({ ok, status, json: async () => data });

test('simplifyStreet membuang nomor, RT/RW, dusun', () => {
    assert.equal(A.simplifyStreet('Jl. Papagan No.13, Dusun II, RT 02/RW 05'), 'Jalan Papagan');
});

test('buildGeocodeQueries: 3 kueri bertahap, kosong bila jalan kosong', () => {
    const q = A.buildGeocodeQueries(parts);
    assert.equal(q.length, 3);
    assert.match(q[0], /Makamhaji/);
    assert.doesNotMatch(q[1], /Makamhaji/);
    assert.deepEqual(A.buildGeocodeQueries({ ...parts, street: '  ' }), []);
});

test('pickResult membuang hasil jauh dari pusat wilayah', () => {
    const far = { lat: '-6.2', lon: '106.8' };
    const near = { lat: '-7.567', lon: '110.778' };
    assert.equal(A.pickResult([far], center, 8), null);
    assert.deepEqual(A.pickResult([far, near], center, 8), { lat: -7.567, lng: 110.778 });
});

test('nominatimUrl memakai viewbox terikat di sekitar pusat', () => {
    const u = A.nominatimUrl('x', center, 0.06);
    assert.match(u, /bounded=1/);
    assert.match(u, /viewbox=/);
});

test('geocodeAddress: lanjut ke kueri berikutnya bila kosong atau hasil salah kota', async () => {
    const calls = [];
    const fetchFn = async (url) => {
        calls.push(url);
        if (calls.length === 1) return resp([]);
        if (calls.length === 2) return resp([{ lat: '-6.2', lon: '106.8' }]);
        return resp([{ lat: '-7.57', lon: '110.78' }]);
    };
    const sleeps = [];
    const r = await A.geocodeAddress({ parts, center, fetchFn, sleep: async (ms) => sleeps.push(ms) });
    assert.equal(r.step, 2);
    assert.equal(calls.length, 3);
    assert.deepEqual(sleeps, [1100, 1100]);
});

test('geocodeAddress: null bila semua kueri gagal, error bila HTTP gagal', async () => {
    const none = await A.geocodeAddress({ parts, center, fetchFn: async () => resp([]), sleep: async () => {} });
    assert.equal(none, null);
    await assert.rejects(A.geocodeAddress({ parts, center, fetchFn: async () => resp([], false, 429), sleep: async () => {} }), /HTTP 429/);
});

test('stripRegion membuang segmen wilayah dan kode pos, jalan tetap', () => {
    const full = 'Jalan belimbing No.10, RT.3/RW.10, Makamhaji, Kec. Kartasura, Kabupaten Sukoharjo, Jawa Tengah 57161';
    assert.equal(A.stripRegion(full, parts), 'Jalan belimbing No.10, RT.3/RW.10');
    assert.equal(A.stripRegion('Jalan Makamhaji Raya', parts), 'Jalan Makamhaji Raya');
});

test('kueri geocode dari alamat yang ditempel lengkap tidak menggandakan wilayah', () => {
    const q = A.buildGeocodeQueries({ ...parts, street: 'Jalan Papagan No.13, Makamhaji, Kec. Kartasura, Kabupaten Sukoharjo, Jawa Tengah 57161' });
    assert.equal(q[0], 'Jalan Papagan, Makamhaji, Kartasura, Kabupaten Sukoharjo');
});
