<?php

namespace App\Services;

use App\Models\Router;
use RouterosAPI;
use Exception;
use Illuminate\Support\Collection;

class MikrotikService
{
    /**
     * Connect, verify, and provision Radius configuration on the MikroTik.
     * * @param array $credentials Contains host, api_username, api_password, port, and radius_secret.
     * @return bool
     */
    public function verifyAndProvision(array $credentials): bool
    {
        $api = new RouterosAPI();
        $api->debug = false;

        // 1. Initial Connection Attempt
        if (
            !$api->connect(
                $credentials["host"],
                $credentials["api_username"],
                $credentials["api_password"],
                $credentials["port"],
            )
        ) {
            return false;
        }

        try {
            // 2. Add Radius Client
            // This allows the MikroTik to authenticate users against our server.
            $api->comm("/radius/add", [
                "service" => "ppp,hotspot",
                "address" => config("radius.server_ip"), // Defined in config/radius.php
                "secret" => $credentials["radius_secret"],
                "comment" => "MANAGED_BY_AAA_SYSTEM",
            ]);

            // 3. Enable Radius Incoming (CoA)
            // Essential for real-time speed changes and disconnects.
            $api->comm("/radius/incoming/set", [
                "accept" => "yes",
                "port" => "3799",
            ]);

            // 4. Force API service to be enabled on the target port
            $api->comm("/ip/service/set", [
                ".id" => "api",
                "disabled" => "no",
                "port" => (string) $credentials["port"],
            ]);

            $api->disconnect();
            return true;
        } catch (Exception $e) {
            // Log error here if needed: \Log::error($e->getMessage());
            if ($api->connected) {
                $api->disconnect();
            }
            return false;
        }
    }

    /**
     * Fetch IP pools from the router and format for our DB.
     */
    public function fetchPools(Router $router): Collection
    {
        $api = new RouterosAPI();

        if (
            $api->connect(
                $router->host,
                $router->api_username,
                $router->api_password, // Decrypted via model cast
                $router->api_port,
            )
        ) {
            $pools = $api->comm("/ip/pool/print");
            $api->disconnect();

            return collect($pools)->map(
                fn($pool) => [
                    "name" => $pool["name"] ?? "Unknown",
                    "range" => $pool["ranges"] ?? "Unknown", // MikroTik uses 'ranges' (plural)
                ],
            );
        }

        return collect();
    }
}
