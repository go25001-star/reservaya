<?php

namespace App\Http\Middleware;

use App\Models\StaffHotel;
use Closure;

class VerificarStaffActivo
{
    public function handle($request, Closure $next)
    {
        $userId = auth('api')->id();

        $staffActivo = StaffHotel::where('user_id', $userId)
            ->where('estado', true)
            ->exists();

        if (!$staffActivo) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tu cuenta ha sido desactivada'
            ], 403);
        }

        return $next($request);
    }
}