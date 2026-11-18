<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sales extends Model
{
    use HasFactory;

    protected $table = 'sales';

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'employee_code',
        'status',
    ];

    /**
     * Relasi ke akun user login.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke rute-rute penugasan sales.
     */
    public function routes(): HasMany
    {
        return $this->hasMany(SalesRoute::class);
    }

    /**
     * Scope untuk sales yang aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Cek apakah sales berstatus aktif.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
