<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\MikrotikService;
use App\Models\Router;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    /**
     * Test the connection without saving or provisioning.
     * Called by the "Test Connection" button.
     */
    public function test(Request $request)
    {
        $validated = $request->validate([
            "host" => "required|string",
            "api_username" => "required|string",
            "api_password" => "required|string",
            "api_port" => "required|integer",
        ]);

        try {
            $mikrotik = app(MikrotikService::class);

            // Just attempt a basic connection/handshake
            $connected = $mikrotik->connect(
                $validated["host"],
                $validated["api_username"],
                $validated["api_password"],
                $validated["api_port"],
            );

            if (!$connected) {
                return response()->json(
                    [
                        "message" =>
                            "Connection refused by the MikroTik hardware.",
                    ],
                    422,
                );
            }

            return response()->json(["status" => "connected"]);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }

    /**
     * The final provisioning and save step.
     * Called by the "Verify & Provision" button.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "host" => "required|string",
            "api_username" => "required|string",
            "api_password" => "required|string",
            "api_port" => "required|integer",
            "model" => "required|string",
        ]);

        Log::info("MIKROTIK_PROVISIONING_START", [
            "host" => $validated["host"],
        ]);

        try {
            $mikrotik = app(MikrotikService::class);

            // Generate a unique Radius Secret for this specific router
            $radiusSecret = Str::random(16);

            // 1. Execute the MikroTik API provisioning (Radius, Incoming, API Service)
            $success = $mikrotik->verifyAndProvision([
                "host" => $validated["host"],
                "api_username" => $validated["api_username"],
                "api_password" => $validated["api_password"],
                "port" => $validated["api_port"],
                "radius_secret" => $radiusSecret,
            ]);

            if (!$success) {
                return response()->json(
                    [
                        "title" => "Provisioning Failed",
                        "message" =>
                            "Connected, but could not apply configuration to the hardware.",
                        "code" => "PROVISIONING_ERROR",
                    ],
                    422,
                );
            }

            // 2. SUCCESS: Save to your 'routers' table
            $router = Router::create([
                "tenant_id" => Auth::user()->tenant_id,
                "site_id" => Auth::user()->current_site_id ?? 1, // Fallback to 1 if not set
                "hardware_name" => $validated["name"], // Identity
                "name" => $validated["name"], // Management Name
                "model" => $validated["model"],
                "hostname" => $validated["host"],
                "api_port" => $validated["api_port"],
                "api_username" => $validated["api_username"],
                "api_password" => encrypt($validated["api_password"]), // Encrypt for security
                "radius_secret" => $radiusSecret,
                "os_version" => "v7", // Defaulting to v7 as per migration
                "is_online" => true,
            ]);

            return response()->json([
                "status" => "success",
                "redirect" => route("dashboard"),
            ]);
        } catch (\Throwable $e) {
            Log::error("PROVISIONING_CRASH", ["error" => $e->getMessage()]);

            return response()->json(
                [
                    "title" => "Server Error",
                    "message" =>
                        "An error occurred while saving the router: " .
                        $e->getMessage(),
                    "code" => "INTERNAL_ERROR",
                ],
                500,
            );
        }
    }
}
