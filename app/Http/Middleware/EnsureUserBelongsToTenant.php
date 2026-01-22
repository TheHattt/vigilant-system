<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserBelongsToTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // If no user is authenticated, allow the request to proceed : if noboby is logged, no blocking.
        if (!$user) {
            return $next($request);
        }

        //Data integrity check : Does user belong to a tenant ? does site exists ?

        if (!user->site_id || !$user->site) {
            // Logout immediately
            Auth::logout();
            return redirect()
                ->route("login")
                ->with(
                    "error",
                    "We could not find an active site linked to your account. Please contact support for assistance.",
                );
        }

        // Cross tenant security check : site accessing site which it doesn't belong to .
        // Grab site or tenant from url
        $siteFromUrl = $request->route("site");

        if ($siteFromUrl && $user->site_id != $siteFromUrl->id) {
            abort(403, "You are not authorized to access this resource.");
        }

        //Site status check : is tenant active , suspended or expired.

        if (!user->site->is_active) {
            return response()->view("errors.inactive-site", [], 403);
        }

        return $next($request);
    }
}
