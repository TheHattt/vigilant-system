<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Router;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    protected $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    /**
     * Handle the infrastructure onboarding process.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Validate Input
        $request->validate([
            "name" => "required|string|max:100",
            "host" => "required|string",
            "api_username" => "required|string",
            "api_password" => "required|string",
            "api_port" => "required|integer",
            "model" => "nullable|string",
            "os_version" => "required|in:v6,v7",
        ]);

        // 2. Generate a Radius Secret automatically
        // This is the secret the MikroTik will use to talk to our server
        $radiusSecret = Str::random(16);

        // 3. Hardware Handshake & Auto-Provisioning
        // We attempt to log in and set up the Radius client on the MikroTik
        $provisioned = $this->mikrotik->verifyAndProvision([
            "host" => $request->host,
            "api_username" => $request->api_username,
            "api_password" => $request->api_password,
            "api_port" => $request->api_port,
            "radius_secret" => $radiusSecret,
        ]);

        if (!$provisioned) {
            return response()->json(
                [
                    "error" => true,
                    "code" => "MIKROTIK_CONNECTION_FAILED",
                    "title" => "Router connection failed",
                    "message" =>
                        "Unable to establish a connection with the MikroTik device.",
                    "context" => [
                        "ip_address" => $request->host,
                        "port" => (int) $request->api_port,
                        "transport" => "api",
                        "timeout_ms" => 5000,
                    ],
                    "retryable" => true,
                    "support_hint" =>
                        "Verify API service status (/ip service) and firewall accessibility on the MikroTik.",
                ],
                422,
            );
        }
        // 4. Create Site
        $site = Site::firstOrCreate([
            "tenant_id" => $user->tenant_id,
            "name" => "Primary Site",
        ]);

        // 5. Save the Router
        $router = Router::create([
            "tenant_id" => $user->tenant_id,
            "site_id" => $site->id,
            "name" => $request->name,
            "host" => $request->host,
            "api_port" => $request->api_port,
            "api_username" => $request->api_username,
            "api_password" => $request->api_password,
            "radius_secret" => $radiusSecret, // Saved for later AAA actions
            "model" => $request->model,
            "os_version" => $request->os_version,
            "is_online" => true,
        ]);

        return response()->json([
            "redirect" => route("dashboard"),
            "message" => "Infrastructure linked and Radius provisioned!",
        ]);
    }
}
