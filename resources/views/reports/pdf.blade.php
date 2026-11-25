<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Kunjungan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; }
        h1 { font-size: 15px; margin: 0 0 2px 0; }
        .muted { color: #64748b; }
        .meta { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        table.summary td { border: 1px solid #e2e8f0; padding: 6px 8px; width: 20%; }
        table.summary .label { font-size: 8px; color: #64748b; }
        table.summary .value { font-size: 13px; font-weight: bold; }
        table.rows { margin-top: 12px; }
        table.rows th { background: #f1f5f9; text-align: left; padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 8px; }
        table.rows td { padding: 5px 6px; border: 1px solid #e2e8f0; vertical-align: top; }
        .tag { font-weight: bold; }
        .tag-far { color: #be123c; }
        .tag-skip { color: #92400e; }
        .tag-upd { color: #1d4ed8; }
        .note { margin-top: 8px; color: #92400e; }
        img.thumb { width: 48px; height: auto; }
    </style>
</head>
<body>
    @php
        $resultLabels = ['successful' => 'Berhasil', 'store_closed' => 'Toko Tutup', 'failed' => 'Ditolak/Gagal'];
    @endphp

    <h1>Laporan Rekapitulasi Kunjungan &amp; Omzet</h1>
    <div class="meta muted">
        Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}
        &nbsp;|&nbsp; Sales: {{ $salesName ?? 'Semua' }}
        @if($flagged) &nbsp;|&nbsp; Hanya yang perlu ditinjau @endif
        &nbsp;|&nbsp; Dibuat: {{ $generatedAt->translatedFormat('d M Y, H:i') }}
    </div>

    <table class="summary">
        <tr>
            <td><div class="label">Kunjungan dijadwalkan</div><div class="value">{{ number_format($summary['total']) }}</div></td>
            <td><div class="label">Selesai check-in</div><div class="value">{{ number_format($summary['completed']) }}</div></td>
            <td><div class="label">Berhasil (closing)</div><div class="value">{{ number_format($summary['successful']) }}</div></td>
            <td><div class="label">Perlu ditinjau</div><div class="value">{{ number_format($summary['flagged']) }}</div></td>
            <td><div class="label">Total omzet</div><div class="value">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</div></td>
        </tr>
    </table>

    <table class="rows">
        <thead>
            <tr>
                <th>Check-in</th>
                <th>Rute</th>
                <th>Sales</th>
                <th>Toko</th>
                <th>Hasil</th>
                <th>Nominal</th>
                <th>Alasan kendala</th>
                <th>Penanda</th>
                @if($withPhotos)<th>Foto</th>@endif
            </tr>
        </thead>
        <tbody>
            @forelse($visits as $v)
                @php
                    $gps = $v->gpsStatus();
                    $updateCount = $v->auditLogs->where('action', 'checkin_updated')->count();
                @endphp
                <tr>
                    <td>{{ $v->checked_in_at ? $v->checked_in_at->translatedFormat('d M Y H:i') : '-' }}</td>
                    <td>{{ $v->salesRoute->name ?? '-' }}</td>
                    <td>{{ $v->salesRoute->sales->name ?? '-' }}</td>
                    <td>{{ $v->store->name ?? '-' }}</td>
                    <td>{{ $v->result ? ($resultLabels[$v->result] ?? $v->result) : 'Belum' }}</td>
                    <td>{{ $v->order ? 'Rp ' . number_format($v->order->total_amount, 0, ',', '.') : '-' }}</td>
                    <td>{{ $v->failure_reason ?? '-' }}</td>
                    <td>
                        @if($v->checked_in_at)
                            @if($gps === 'far')
                                <div class="tag tag-far">Lokasi jauh {{ number_format($v->gps_distance_m, 0, ',', '.') }} m</div>
                            @elseif($gps === 'unknown')
                                <div class="muted">Lokasi tidak terverifikasi</div>
                            @endif
                            @if($v->skip_reason)
                                <div class="tag tag-skip">Lompat urutan: {{ $v->skip_reason }}</div>
                            @endif
                            @if($v->clarification_requested_at)
                                <div class="tag tag-skip">{{ $v->hasOpenClarification() ? 'Menunggu klarifikasi' : 'Klarifikasi dijawab' }}</div>
                            @endif
                            @if($updateCount > 0)
                                <div class="tag tag-upd">Diperbarui {{ $updateCount }}x</div>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    @if($withPhotos)
                        <td>
                            @if(!empty($thumbs[$v->id]))
                                <img class="thumb" src="{{ $thumbs[$v->id] }}" alt="Foto">
                            @else
                                -
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $withPhotos ? 9 : 8 }}" style="text-align:center; padding: 14px;" class="muted">
                        Tidak ada data kunjungan pada rentang tanggal yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($truncatedRows > 0)
        <div class="note">Catatan: {{ number_format($truncatedRows) }} baris lainnya tidak dimuat (batas 500 baris per PDF). Persempit rentang tanggal atau filter sales.</div>
    @endif
    @if($withPhotos && $visits->count() > $photoLimit)
        <div class="note">Catatan: foto hanya dimuat untuk {{ $photoLimit }} kunjungan pertama.</div>
    @endif
</body>
</html>
