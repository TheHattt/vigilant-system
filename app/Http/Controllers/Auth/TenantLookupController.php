<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class TenantLookupController extends Controller
{
    /**
     * Handle the tenant prefix lookup for a given email.
     */
    public function __invoke(string $email)
    {
        // Use md5 to ensure the cache key is a valid, consistent string
        return Cache::remember(
            "tenant_lookup_" . md5($email),
            86400,
            function () use ($email) {
                $user = User::where("email", $email)->first(["tenant_id"]);

                return [
                    "prefix" => $user?->tenant?->account_prefix ?? null,
                ];
            },
        );
    }
}
