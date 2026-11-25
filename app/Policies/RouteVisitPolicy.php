<?php

namespace App\Policies;

use App\Models\RouteVisit;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Aturan akses kunjungan: admin memantau (lihat + minta klarifikasi), sales hanya mengelola kunjungan miliknya.
 */
class RouteVisitPolicy
{
    private function owns(User $user, RouteVisit $visit): bool
    {
        return $user->isSales() && ($visit->salesRoute->sales->user_id ?? null) === $user->id;
    }

    /** Admin boleh melihat semua; sales hanya kunjungan miliknya. */
    public function view(User $user, RouteVisit $visit): Response
    {
        return $user->isAdmin() || $this->owns($user, $visit)
            ? Response::allow()
            : Response::deny('Akses ditolak. Anda tidak berhak melihat atau mengakses kunjungan sales lain.');
    }

    /** Hanya sales pemilik kunjungan yang boleh mencatat atau mengubah check-in. */
    public function checkin(User $user, RouteVisit $visit): Response
    {
        if ($user->isSales() && !$this->owns($user, $visit)) {
            return Response::deny('Akses ditolak. Anda tidak berwenang mencatat check-in pada kunjungan sales lain.');
        }

        return $this->owns($user, $visit)
            ? Response::allow()
            : Response::deny('Hanya sales pemilik kunjungan yang dapat mencatat atau mengubah check-in. Admin dapat meminta klarifikasi.');
    }

    /** Hanya admin yang boleh meminta klarifikasi. */
    public function requestClarification(User $user, RouteVisit $visit): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Hanya admin yang dapat meminta klarifikasi.');
    }
}
