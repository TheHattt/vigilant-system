<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class TenantLookupController extends Controller
{
    /**
     * Handle identity lookup.
     * ISP Admins are identified by their Tenant relationship.
     * Staff are identified by their Site relationship.
     */
    public function __invoke(string $email): JsonResponse
    {
        // Load both relationships.
        // Admin ISP usually has tenant_id. Staff usually has site_id.
        $user = User::with(["tenant", "site"])
            ->where("email", $email)
            ->first();

        if (!$user) {
            return response()->json(["error" => "Identity not found."], 404);
        }

        // 1. Super Admins (Developers/Platform Owners)
        if ($user->is_super_admin) {
            return response()->json([
                "type" => "super_admin",
                "prefix" => null,
            ]);
        }

        // 2. ISP Admin (The Owner/Registrant)
        // We prioritize the 'tenant' slug so they can login even with 0 sites.
        if ($user->tenant) {
            return response()->json([
                "type" => "tenant_user",
                "prefix" => $user->tenant->slug,
                "name" => $user->tenant->name,
            ]);
        }

        // 3. Staff Members (Restricted to a Site)
        if ($user->site) {
            return response()->json([
                "type" => "tenant_user",
                "prefix" => $user->site->slug,
                "name" => $user->site->name,
            ]);
        }

        // 4. Fallback: User exists but is detached from the system
        return response()->json(
            [
                "error" =>
                    "Account active but no ISP instance assigned. Please contact support.",
            ],
            422,
        );
    }
}
