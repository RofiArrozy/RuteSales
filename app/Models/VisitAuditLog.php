<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitAuditLog extends Model
{
    protected $table = 'visit_audit_logs';

    protected $fillable = [
        'route_visit_id',
        'user_id',
        'action',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function routeVisit(): BelongsTo
    {
        return $this->belongsTo(RouteVisit::class, 'route_visit_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
