<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VisitPhoto;
use Illuminate\Auth\Access\Response;

class VisitPhotoPolicy
{
    /** Foto bukti: admin atau sales pemilik kunjungan. */
    public function view(User $user, VisitPhoto $photo): Response
    {
        $ownerUserId = $photo->routeVisit->salesRoute->sales->user_id ?? null;

        return $user->isAdmin() || ($user->isSales() && $ownerUserId === $user->id)
            ? Response::allow()
            : Response::deny('Akses ditolak. Anda tidak berhak melihat foto kunjungan sales lain.');
    }
}
