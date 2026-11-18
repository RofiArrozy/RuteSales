<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_name',
        'phone',
        'address',
        'area',
        'latitude',
        'longitude',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * Relasi ke kunjungan rute toko.
     */
    public function routeVisits(): HasMany
    {
        return $this->hasMany(RouteVisit::class);
    }

    /**
     * Scope untuk toko yang masih aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Cek apakah toko aktif.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
