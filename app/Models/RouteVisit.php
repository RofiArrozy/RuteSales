<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RouteVisit extends Model
{
    use HasFactory;

    protected $table = 'route_visits';

    protected $fillable = [
        'sales_route_id',
        'store_id',
        'sequence',
        'result',
        'checked_in_at',
        'notes',
        'failure_reason',
        'skip_reason',
        'simulated_latitude',
        'simulated_longitude',
        'gps_accuracy_m',
        'gps_distance_m',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
            'simulated_latitude' => 'float',
            'simulated_longitude' => 'float',
            'sequence' => 'integer',
            'gps_accuracy_m' => 'integer',
            'gps_distance_m' => 'integer',
        ];
    }

    /**
     * Relasi ke rute induk.
     */
    public function salesRoute(): BelongsTo
    {
        return $this->belongsTo(SalesRoute::class, 'sales_route_id');
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(SalesRoute::class, 'sales_route_id');
    }

    /**
     * Relasi ke toko tujuan kunjungan.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Relasi ke catatan pesanan (1:1).
     */
    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    /**
     * Relasi ke foto bukti kunjungan (1 sampai 3 foto).
     */
    public function photos(): HasMany
    {
        return $this->hasMany(VisitPhoto::class, 'route_visit_id')->orderBy('id');
    }

    /**
     * Scope: check-in yang ditandai "jauh dari toko". Selaras dengan gpsStatus() === 'far'.
     * (Memakai penjumlahan, bukan pengurangan, agar aman untuk kolom unsigned di MySQL.)
     */
    public function scopeFar($query)
    {
        $radius = (int) config('rutesales.gps_radius_m', 200);
        $maxAccuracy = (int) config('rutesales.gps_max_accuracy_m', 500);

        return $query->whereNotNull('gps_distance_m')
            ->whereRaw('COALESCE(gps_accuracy_m, 0) <= ?', [$maxAccuracy])
            ->whereRaw('gps_distance_m > ? + COALESCE(gps_accuracy_m, 0)', [$radius]);
    }

    /**
     * Scope: kunjungan yang perlu ditinjau admin (lokasi jauh, melompat urutan, atau check-in diperbarui).
     */
    public function scopeFlagged($query)
    {
        return $query->where(function ($q) {
            $q->far()
                ->orWhereNotNull('skip_reason')
                ->orWhereHas('auditLogs', fn ($a) => $a->where('action', 'checkin_updated'));
        });
    }

    /**
     * Relasi ke riwayat audit check-in.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(VisitAuditLog::class, 'route_visit_id')->orderBy('id');
    }

    /**
     * Status verifikasi lokasi check-in: unknown | near | far.
     * "far" hanya jika jarak dikurangi akurasi GPS masih melebihi radius.
     */
    public function gpsStatus(): string
    {
        if (is_null($this->gps_distance_m)) {
            return 'unknown';
        }

        $accuracy = $this->gps_accuracy_m ?? 0;

        if ($accuracy > config('rutesales.gps_max_accuracy_m', 500)) {
            return 'unknown';
        }

        $effectiveDistance = max(0, $this->gps_distance_m - $accuracy);

        return $effectiveDistance > config('rutesales.gps_radius_m', 200) ? 'far' : 'near';
    }

    /**
     * Cek apakah kunjungan telah di check-in.
     */
    public function isCheckedIn(): bool
    {
        return !is_null($this->checked_in_at);
    }

    /**
     * Cek apakah kunjungan berstatus sukses.
     */
    public function isSuccessful(): bool
    {
        return $this->result === 'successful';
    }

    /**
     * Cek apakah toko tutup.
     */
    public function isStoreClosed(): bool
    {
        return $this->result === 'store_closed';
    }

    /**
     * Cek apakah kunjungan gagal.
     */
    public function isFailed(): bool
    {
        return $this->result === 'failed';
    }
}
