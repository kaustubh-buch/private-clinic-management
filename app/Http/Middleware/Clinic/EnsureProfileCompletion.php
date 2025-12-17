<?php

namespace App\Http\Middleware\Clinic;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileCompletion
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->hasRole(config('constants.GLOBAL.ROLES.DOCTOR'))) {
            $user = Auth::user();

            if ($user->is_fully_registered == 0 || ($user->clinics && $user->clinics->status != config('constants.CLINICS.STATUS.APPROVED'))) {
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
