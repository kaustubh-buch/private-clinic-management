<?php

namespace App\Http\Middleware\Clinic;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectAuthenticatedClinic
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            if (Auth::user()->hasRole(config('constants.GLOBAL.ROLES.DOCTOR'))) {
                return redirect()->route('dashboard');
            }

            if (Auth::user()->hasRole(config('constants.GLOBAL.ROLES.ADMIN'))) {
                return redirect()->route('admin.dashboard');
            }
        }

        return $next($request);
    }
}
