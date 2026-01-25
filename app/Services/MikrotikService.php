<?php

namespace App\Services;

use App\Models\Router;
use App\Models\PppSecret;
use App\Models\PppLiveSession;
use RouterOS\Client;
use RouterOS\Query;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MikrotikService
{
    private const CONNECTION_TIMEOUT = 3;
    private const READ_TIMEOUT = 5;
    private const CACHE_TTL_RESOURCES = 5;
    private const CACHE_TTL_PPP_SECRETS = 10;
    private const CACHE_TTL_INTERFACES = 30;
    private const CIRCUIT_BREAKER_DURATION = 5;

    /**
     * Get the configuration export from the MikroTik router.
     */
    public function getExport(Router $router): string
    {
        try {
            $client = $this->getClient($router);
            $responses = $client->query("/export")->read();

            // MikroTik export usually returns an array of lines or sections
            if (is_array($responses)) {
                return collect($responses)
                    ->map(function ($item) {
                        return $item[".section"] ??
                            (is_string($item) ? $item : "");
                    })
                    ->implode("\n");
            }

            return (string) $responses;
        } catch (\Throwable $e) {
            Log::error(
                "Export failed for router {$router->id}: " . $e->getMessage(),
            );
            throw $e;
        }
    }

    /**
     * Sync active sessions from MikroTik and store them in the local database.
     */
    public function syncLiveSessions(Router $router): void
    {
        if ($this->isCircuitBreakerActive($router->id)) {
            return;
        }

        try {
            $activeConnections = $this->getPPPActive($router);

            // 1. Get IDs of secrets belonging to this router to match them
            $secretIds = $router->pppSecrets()->pluck("id", "name")->toArray();

            // 2. Prepare data for current active sessions
            foreach ($activeConnections as $session) {
                $name = $session["name"] ?? null;

                if ($name && isset($secretIds[$name])) {
                    PppLiveSession::updateOrCreate(
                        ["ppp_secret_id" => $secretIds[$name]],
                        [
                            "name" => $name,
                            "service" => $session["service"] ?? "pppoe",
                            "caller_id" => $session["caller-id"] ?? null,
                            "address" => $session["address"] ?? null,
                            "uptime" => $session["uptime"] ?? null,
                            "bytes_in" => (int) ($session["bytes-in"] ?? 0),
                            "bytes_out" => (int) ($session["bytes-out"] ?? 0),
                        ],
                    );
                }
            }

            // 3. Optional: Remove sessions from DB that are no longer active on Router
            $activeNames = collect($activeConnections)
                ->pluck("name")
                ->toArray();
            PppLiveSession::whereIn("ppp_secret_id", array_values($secretIds))
                ->whereNotIn("name", $activeNames)
                ->delete();
        } catch (\Throwable $e) {
            Log::error(
                "Live session sync failed for router {$router->id}: " .
                    $e->getMessage(),
            );
        }
    }

    public function getCachedData(
        Router $router,
        string $type,
        int $seconds = 5,
    ): ?array {
        $breakerKey = "router.{$router->id}.unreachable";
        if (Cache::has($breakerKey)) {
            return null;
        }

        $cacheKey = "router.{$router->id}.{$type}";

        return Cache::remember($cacheKey, $seconds, function () use (
            $router,
            $type,
            $breakerKey,
        ) {
            try {
                $client = $this->getClient($router);

                $data = match ($type) {
                    "ppp-secrets" => $client
                        ->query("/ppp/secret/print")
                        ->read() ?? [],
                    "resources" => $client
                        ->query("/system/resource/print")
                        ->read()[0] ?? [],
                    "interfaces" => $client
                        ->query("/interface/print")
                        ->read() ?? [],
                    default => null,
                };

                return $data;
            } catch (\Throwable $e) {
                Cache::put(
                    $breakerKey,
                    true,
                    now()->addMinutes(self::CIRCUIT_BREAKER_DURATION),
                );
                Log::warning(
                    "Router {$router->id} unreachable. Circuit breaker active.",
                    [
                        "router_id" => $router->id,
                        "router_name" => $router->name,
                        "type" => $type,
                        "error" => $e->getMessage(),
                    ],
                );
                return null;
            }
        });
    }

    public function getPPPSecrets(Router $router): array
    {
        return $this->getCachedData(
            $router,
            "ppp-secrets",
            self::CACHE_TTL_PPP_SECRETS,
        ) ?? [];
    }

    public function getActiveConnections(
        Router $router,
        bool $silent = false,
    ): array {
        $breakerKey = "router.{$router->id}.unreachable";

        if (Cache::has($breakerKey)) {
            return [];
        }

        try {
            $client = $this->getClient($router);
            return $client->query("/ppp/active/print")->read() ?? [];
        } catch (\Throwable $e) {
            Cache::put(
                $breakerKey,
                true,
                now()->addMinutes(self::CIRCUIT_BREAKER_DURATION),
            );

            if (!$silent) {
                Log::warning(
                    "Failed to get active connections for router {$router->id}",
                    [
                        "router_id" => $router->id,
                        "error" => $e->getMessage(),
                    ],
                );
            }

            return [];
        }
    }

    public function createPPPSecret(Router $router, array $data): array
    {
        $breakerKey = "router.{$router->id}.unreachable";

        try {
            $client = $this->getClient($router);

            $response = $client
                ->query("/ppp/secret/add", [
                    "name" => $data["name"],
                    "password" => $data["password"],
                    "service" => $data["service"] ?? "any",
                    "profile" => $data["profile"] ?? "default",
                    "comment" => $data["comment"] ?? "",
                ])
                ->read();

            $this->clearCache($router->id);
            Cache::forget($breakerKey);

            return $response;
        } catch (\Throwable $e) {
            Cache::put(
                $breakerKey,
                true,
                now()->addMinutes(self::CIRCUIT_BREAKER_DURATION),
            );

            Log::error("Failed to create PPP secret on router {$router->id}", [
                "router_id" => $router->id,
                "name" => $data["name"] ?? "unknown",
                "error" => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function updatePPPSecret(
        Router $router,
        PppSecret $secret,
        array $data,
    ): bool {
        $breakerKey = "router.{$router->id}.unreachable";

        try {
            $client = $this->getClient($router);
            $query = new Query("/ppp/secret/set");

            $query->equal(".id", $secret->mikrotik_id ?? $secret->name);

            if (isset($data["name"])) {
                $query->equal("name", $data["name"]);
            }
            if (isset($data["password"])) {
                $query->equal("password", $data["password"]);
            }
            if (isset($data["profile"])) {
                $query->equal("profile", $data["profile"]);
            }
            if (isset($data["service"])) {
                $query->equal("service", $data["service"]);
            }
            if (isset($data["comment"])) {
                $query->equal("comment", $data["comment"]);
            }
            if (isset($data["is_active"])) {
                $query->equal("disabled", $data["is_active"] ? "no" : "yes");
            }

            $client->query($query)->read();

            $this->clearCache($router->id);
            Cache::forget($breakerKey);

            return true;
        } catch (\Throwable $e) {
            Cache::put(
                $breakerKey,
                true,
                now()->addMinutes(self::CIRCUIT_BREAKER_DURATION),
            );

            Log::error("Failed to update PPP secret on router {$router->id}", [
                "router_id" => $router->id,
                "secret_id" => $secret->id,
                "error" => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function deletePPPSecret(Router $router, PppSecret $secret): bool
    {
        $breakerKey = "router.{$router->id}.unreachable";

        try {
            $client = $this->getClient($router);
            $query = new Query("/ppp/secret/remove");
            $query->equal(".id", $secret->mikrotik_id ?? $secret->name);

            $client->query($query)->read();

            $this->clearCache($router->id);
            Cache::forget($breakerKey);

            return true;
        } catch (\Throwable $e) {
            Cache::put(
                $breakerKey,
                true,
                now()->addMinutes(self::CIRCUIT_BREAKER_DURATION),
            );

            Log::error("Failed to delete PPP secret on router {$router->id}", [
                "router_id" => $router->id,
                "secret_id" => $secret->id,
                "error" => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function testConnection(Router $router): bool
    {
        try {
            $client = $this->getClient($router);
            $client->query("/system/identity/print")->read();
            Cache::forget("router.{$router->id}.unreachable");
            return true;
        } catch (\Throwable $e) {
            Log::debug(
                "Connection test failed for router {$router->id}: {$e->getMessage()}",
            );
            return false;
        }
    }

    public function getSystemResources(Router $router): array
    {
        return $this->getCachedData(
            $router,
            "resources",
            self::CACHE_TTL_RESOURCES,
        ) ?? [];
    }

    public function getInterfaces(Router $router): array
    {
        return $this->getCachedData(
            $router,
            "interfaces",
            self::CACHE_TTL_INTERFACES,
        ) ?? [];
    }

    public function getPPPActive(Router $router): array
    {
        try {
            $client = $this->getClient($router);
            return $client->query("/ppp/active/print")->read() ?? [];
        } catch (\Throwable $e) {
            Log::warning(
                "Failed to get PPP active list for router {$router->id}: {$e->getMessage()}",
            );
            return [];
        }
    }

    private function getClient(Router $router): Client
    {
        try {
            $config = [
                "host" => $router->hostname ?? $router->host,
                "user" => $router->api_username,
                "pass" => $router->api_password,
                "port" => (int) $router->api_port,
                "timeout" => self::CONNECTION_TIMEOUT,
            ];

            $client = new Client($config);

            if (method_exists($client, "setTimeout")) {
                $client->setTimeout(self::READ_TIMEOUT);
            }

            return $client;
        } catch (\Throwable $e) {
            throw new \Exception(
                "Failed to connect to router: " . $e->getMessage(),
            );
        }
    }

    private function clearCache(int $routerId): void
    {
        $keys = [
            "router.{$routerId}.ppp-secrets",
            "router.{$routerId}.active_list",
            "router.{$routerId}.online_count",
            "router.{$routerId}.resources",
            "router.{$routerId}.interfaces",
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    public function clearCircuitBreaker(int $routerId): void
    {
        Cache::forget("router.{$routerId}.unreachable");
        Log::info("Circuit breaker manually cleared for router {$routerId}");
    }

    public function isCircuitBreakerActive(int $routerId): bool
    {
        return Cache::has("router.{$routerId}.unreachable");
    }
}
