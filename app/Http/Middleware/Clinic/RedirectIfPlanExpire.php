<?php

namespace App\Http\Middleware\Clinic;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfPlanExpire
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if the user's subscription plan is expired or active.
     * If expired, it redirects the user to the appropriate route based on the grace period or allowed routes.
     *
     * @param Request                      $request The incoming HTTP request.
     * @param Closure(Request): (Response) $next    The next middleware in the pipeline.
     *
     * @return Response The HTTP response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip middleware for admin users
        if (! $user->hasRole('admin')) {
            $latestSubscription = $user->latestSubscription;

            // Redirect to dashboard if no subscription exists
            if (! $latestSubscription) {
                return $this->handleRedirect($request, 'dashboard');
            }

            $routeName = $request->route()->getName();

            // Check if the user has failed subscription payments
            if ($user->hasFailedSubscriptionPayment() || $user->hasSubscriptionPaymentPending()) {
                $isFailed = $user->hasFailedSubscriptionPayment();
                $daysSincePaymentIssue = $isFailed
                    ? $latestSubscription->getDaysSinceLastFailedPayment()
                    : $latestSubscription->getDaysSinceLastPendingPayment();

                $accessRoutes = $daysSincePaymentIssue >= config('constants.PAYMENT_FAILED_RESTRICT_AFTER_DAYS')
                    ? config('constants.PAYMENT_FAILED_AFTER_SEVEN_DAYS_ACCESS_ROUTE')
                    : config('constants.PAYMENT_FAILED_ACCESS_ROUTE');

                if ($this->isRouteAllowed($routeName, $accessRoutes)) {
                    return $next($request);
                }

                return $this->handleRedirect($request, 'subscription.index');
            }

            // Allow access if the subscription is active or not expired
            if (($latestSubscription->subscription_status == config('constants.SUBSCRIPTION_PLAN.STATUS.ACTIVE') && $latestSubscription->subscription_end_date->gte(now())) || ! $latestSubscription->isExpired()) {
                return $next($request);
            }

            // Handle expired subscriptions
            if ($latestSubscription->isExpired()) {
                // Redirect free plan users to the dashboard
                if ($latestSubscription->isFreePlan()) {
                    if ($this->isRouteAllowed($routeName, config('constants.SUBSCRIPTION_ROUTES'))) {
                        return $next($request);
                    }

                    return $this->handleRedirect($request, 'subscription.index');
                }

                $now = now();
                $diff = $latestSubscription->subscription_end_date->diffInDays($now);
                // Determine allowed routes based on grace period
                $allowedPrefixes = $diff <= config('constants.ALLOWED_DAYS_AFTER_CANCELLATION')
                    ? config('constants.ALLOWED_ROUTES_GRACE')
                    : config('constants.ALLOWED_ROUTES_AFTER_GRACE');

                if ($this->isRouteAllowed($routeName, $allowedPrefixes)) {
                    return $next($request);
                }
            }

            // Redirect to subscription page if no allowed routes match
            return $this->handleRedirect($request, 'subscription.index');
        }

        // Allow access for admin users
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
                'message' =>  __('messages.alerts.no_permission_to_access'),
            ], Response::HTTP_FORBIDDEN); // Forbidden
        }

        return redirect()->route($route);
    }
}
