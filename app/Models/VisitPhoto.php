<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitPhoto extends Model
{
    protected $table = 'visit_photos';

    protected $fillable = [
        'route_visit_id',
        'path',
    ];

    /**
     * Relasi ke kunjungan pemilik foto.
     */
    public function routeVisit(): BelongsTo
    {
        return $this->belongsTo(RouteVisit::class, 'route_visit_id');
    }
}
