<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesRoute extends Model
{
    use HasFactory;

    protected $table = 'sales_routes';

    protected $fillable = [
        'sales_id',
        'route_date',
        'name',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'route_date' => 'date',
        ];
    }

    /**
     * Relasi ke sales penanggung jawab rute.
     */
    public function sales(): BelongsTo
    {
        return $this->belongsTo(Sales::class);
    }

    /**
     * Relasi ke daftar kunjungan toko dalam rute (terurut berdasarkan sequence).
     */
    public function visits(): HasMany
    {
        return $this->hasMany(RouteVisit::class)->orderBy('sequence', 'asc');
    }

    /**
     * Cek apakah rute sudah selesai.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Cek apakah rute masih direncanakan.
     */
    public function isPlanned(): bool
    {
        return $this->status === 'planned';
    }

    public function scopePlanned($query)
    {
        return $query->where('status', 'planned');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
