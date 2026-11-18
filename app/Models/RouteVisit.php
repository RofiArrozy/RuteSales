<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'simulated_latitude',
        'simulated_longitude',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
            'simulated_latitude' => 'float',
            'simulated_longitude' => 'float',
            'sequence' => 'integer',
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
