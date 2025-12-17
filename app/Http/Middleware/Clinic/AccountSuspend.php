<?php

namespace App\Http\Middleware\Clinic;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AccountSuspend
{
    /**
     * Handle an incoming request.
     *
     * @param Request                      $request The incoming HTTP request.
     * @param Closure(Request): (Response) $next    The next middleware in the pipeline.
     *
     * @return Response The HTTP response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user() && Auth::user()->hasRole(config('constants.GLOBAL.ROLES.DOCTOR'))) {
            $clinic = Auth::user()->clinics;
            if ($clinic->is_suspended == 1) {
                $routeName = $request->route()->getName();
                if (! $this->isRouteAllowed($routeName, config('constants.ACCOUNT_SUSPEND_ACCESS_ROUTE'))) {
                    return $this->handleRedirect($request, 'dashboard');
                }
            }
        }

        return $next($request);
    }

    /**
     * Check if the given route is allowed based on the allowed prefixes.
     *
     * @param string $routeName       The name of the current route.
     * @param array  $allowedPrefixes The list of allowed route prefixes.
     *
     * @return bool True if the route is allowed, false otherwise.
     */
    private function isRouteAllowed(string $routeName, array $allowedPrefixes): bool
    {
        foreach ($allowedPrefixes as $prefix) {
            if (Str::startsWith($routeName, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle redirection for normal and AJAX/JSON requests.
     *
     * If the request expects JSON (e.g., via AJAX or API), return a JSON response
     * with a redirect URL and message. Otherwise, perform a standard HTTP redirect.
     *
     * @param Request $request The current HTTP request instance.
     * @param string  $route   The named route to redirect to.
     *
     * @return JsonResponse|RedirectResponse
     */
    private function handleRedirect(Request $request, string $route): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'redirect' => route($route),
                'message' => __('messages.alerts.no_permission_to_access'),
            ], Response::HTTP_FORBIDDEN); // Forbidden
        }

        return redirect()->route($route);
    }
}
