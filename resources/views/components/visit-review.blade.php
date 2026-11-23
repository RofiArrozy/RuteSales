@props(['visit'])

{{-- Penanda untuk admin: verifikasi lokasi, lompatan urutan, dan riwayat perubahan check-in. --}}
@if($visit->isCheckedIn())
    @php
        $gps = $visit->gpsStatus();
        $radius = config('rutesales.gps_radius_m');
        $resultLabels = ['successful' => 'Berhasil', 'store_closed' => 'Toko Tutup', 'failed' => 'Ditolak/Gagal'];
        $updates = $visit->auditLogs->where('action', 'checkin_updated');
        $rp = fn ($v) => is_null($v) ? '-' : 'Rp ' . number_format($v, 0, ',', '.');
    @endphp

    <div class="mt-2.5 space-y-1.5 text-xs">
        @if($gps === 'near')
            <div class="text-emerald-700">📍 Lokasi sesuai: ±{{ number_format($visit->gps_distance_m, 0, ',', '.') }} m dari toko (akurasi ±{{ $visit->gps_accuracy_m ?? 0 }} m)</div>
        @elseif($gps === 'far')
            <div class="text-rose-700 font-semibold bg-rose-50 border border-rose-200 rounded-lg px-2.5 py-1.5">
                🚩 Check-in jauh dari toko: {{ number_format($visit->gps_distance_m, 0, ',', '.') }} m
                (akurasi ±{{ $visit->gps_accuracy_m ?? 0 }} m, batas {{ $radius }} m)
            </div>
        @else
            <div class="text-amber-700">📍 Lokasi tidak terverifikasi (tanpa GPS, akurasi rendah, atau toko belum punya koordinat)</div>
        @endif

        @if($visit->skip_reason)
            <div class="text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-2.5 py-1.5">
                ↪ Melewati urutan kunjungan. Alasan: "{{ $visit->skip_reason }}"
            </div>
        @endif

        @if($updates->isNotEmpty())
            <details class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5">
                <summary class="cursor-pointer font-semibold text-slate-600">Check-in diperbarui {{ $updates->count() }} kali (lihat riwayat)</summary>
                <ul class="mt-2 space-y-2 text-slate-600">
                    @foreach($updates as $log)
                        @php
                            $p = $log->payload ?? [];
                            $changes = $p['changes'] ?? [];
                        @endphp
                        <li class="border-t border-slate-100 pt-1.5">
                            <div class="font-semibold text-slate-700">
                                {{ $log->created_at->translatedFormat('d M Y, H:i') }} oleh {{ $log->user->name ?? 'pengguna terhapus' }}
                            </div>
                            @foreach($changes as $field => $change)
                                <div>
                                    @switch($field)
                                        @case('result')
                                            Hasil: {{ $resultLabels[$change['from']] ?? '-' }} → {{ $resultLabels[$change['to']] ?? '-' }}
                                            @break
                                        @case('total_amount')
                                            Nilai pesanan: {{ $rp($change['from']) }} → {{ $rp($change['to']) }}
                                            @break
                                        @case('failure_reason')
                                            Alasan kendala diubah
                                            @break
                                        @case('notes')
                                            Catatan diubah
                                            @break
                                    @endswitch
                                </div>
                            @endforeach
                            @if(!empty($p['photos_added']))
                                <div>Foto ditambah: {{ $p['photos_added'] }}</div>
                            @endif
                            @if(!empty($p['photos_removed']))
                                <div>Foto dihapus: {{ $p['photos_removed'] }}</div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </details>
        @endif
    </div>
@endif
