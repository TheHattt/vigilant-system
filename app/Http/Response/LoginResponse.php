<?php

namespace App\Http\Response;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();
        $redirect = "/dashboard"; // Default fallback

        // 1. SuperAdmin go global (Check if property or method)
        if ($user->is_super_admin) {
            $redirect = "/admin/global";
        }
        // 2. ISP Admins (Has tenant, no site)
        elseif ($user->tenant_id && !$user->site_id) {
            $redirect = "/isp/dashboard";
        }
        // 3. Staff Members (Has a site)
        else {
            $redirect = "/site/dashboard";
        }

        // CRITICAL: This part must execute to tell Alpine.js where to go
        if ($request->wantsJson()) {
            return response()->json(["redirect" => $redirect]);
        }

        return redirect()->intended($redirect);
    }
}
