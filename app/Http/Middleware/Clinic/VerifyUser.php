<?php

namespace App\Http\Middleware\Clinic;

use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyUser extends Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $user = Auth::user();
        if (! $user || ! $user->hasRole(config('constants.GLOBAL.ROLES.DOCTOR'))) {
            $message = ($user && ! $user->hasRole(config('constants.GLOBAL.ROLES.DOCTOR'))) ? __('messages.alerts.doctor_role_required') : __('messages.alerts.logged_out');
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'route' => route('login'),
                ], 403);
            } else {
                return redirect()->route('login');
            }
        }

        if ($user->email_verified_at === null) {
            $message = __('messages.alerts.email_verification_required');

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'route' => route('login'),
                ], 403);
            } else {
                return redirect()->route('login')
                    ->with('error', $message);
            }
        }
        $response = parent::handle($request, $next, ...$guards);

        return $response;
    }
}
