<?php

namespace App\Services;
use App\Models\Router;
use RouterosAPI;

class MikrotikService
{
    /**
     *Attempt to connect to a Mikrotik router using the provided credentials.
     *
     * @param Router $router The router model instance.
     * @param string $username The username for authentication.
     * @param string $password The password for authentication.
     * @return RouterosAPI|null The RouterosAPI instance if successful, null otherwise.
     */
    public function verifyConnection(array $credentials): bool
    {
        $api = new RouterosAPI();
        $api->debug = false;

        if (
            $api->connect(
                $credentials["host"],
                $credentials["api_username"],
                $credentials["api_password"],
                $credentials["port"],
            )
        ) {
            $api->disconnect();
            return true;
        }

        return false;
    }

    /**
     *Fetch IP pools from the router
     **/

    public function fetchPools(Router $router)
    {
        $api = new RouterosAPI();

        if (
            $api->connect(
                $router->host,
                $router->api_username,
                $router->api_password,
                $router->port,
            )
        ) {
            $pools = $api->comm("/ip/pool/print"); // IP Pools
            $api->disconnect(); // Disconnect from the router
            // Return the fetched pools as a collection of arrays
            return collect($pools)->map(
                fn($pool) => [
                    ["name" => $pool["name"] ?? "Unknown"],
                    ["range" => $pool["range"] ?? "Unknown"],
                ],
            );
        }

        return collect();
    }
}
