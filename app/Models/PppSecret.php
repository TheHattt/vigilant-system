<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PppSecret extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "router_id",
        "mikrotik_id",
        "name",
        "password",
        "service",
        "profile",
        "local_address",
        "remote_address",
        "caller_id",
        "rate_limit",
        "routes",
        "is_active",
        "is_synced",
        "comment",
        "last_synced_at",
        "last_connected_at",
    ];

    protected $casts = [
        "is_active" => "boolean",
        "is_synced" => "boolean",
        "last_synced_at" => "datetime",
        "last_connected_at" => "datetime",
        "routes" => "array",
    ];

    // ============================================
    // MAXIMUM PERFORMANCE SYNC
    // ============================================

    /**
     * Sync PPP secrets from MikroTik with MAXIMUM optimization
     *
     * Optimizations applied:
     * - Differential sync (only update what changed)
     * - Chunked processing (prevents memory overflow)
     * - Bulk encryption (faster than per-record)
     * - Index-aware queries
     * - Minimal database round trips
     * - Early validation
     *
     * @param int $routerId
     * @param array $mikrotikSecrets
     * @param bool $forceFull Force full sync instead of differential
     * @return array Sync statistics
     */
    public static function syncFromMikroTik(
        int $routerId,
        array $mikrotikSecrets,
        bool $forceFull = false,
    ): array {
        $startTime = microtime(true);

        // Early exit for empty data
        if (empty($mikrotikSecrets)) {
            logger()->warning("No PPP secrets to sync", [
                "router_id" => $routerId,
            ]);
            return [
                "synced" => 0,
                "created" => 0,
                "updated" => 0,
                "deleted" => 0,
                "duration" => 0,
            ];
        }

        $now = now();
        $stats = [
            "synced" => 0,
            "created" => 0,
            "updated" => 0,
            "deleted" => 0,
        ];

        // Step 1: Get existing secrets (indexed query)
        $existingSecrets = self::getExistingSecretsMap($routerId, $forceFull);

        // Step 2: Prepare records with optimizations
        [$records, $mikrotikIds] = self::prepareRecordsOptimized(
            $routerId,
            $mikrotikSecrets,
            $existingSecrets,
            $now,
        );

        if (empty($records)) {
            logger()->warning("No valid PPP secrets to sync after validation", [
                "router_id" => $routerId,
            ]);
            return array_merge($stats, [
                "duration" => microtime(true) - $startTime,
            ]);
        }

        // Step 3: Perform chunked upsert for large datasets
        $stats["synced"] = self::performChunkedUpsert($records);

        // Step 4: Clean up orphaned records (soft delete)
        $stats["deleted"] = self::cleanupOrphanedSecrets(
            $routerId,
            $mikrotikIds,
            $now,
        );

        // Step 5: Clear related caches
        self::clearRelatedCaches($routerId);

        $duration = microtime(true) - $startTime;
        $stats["duration"] = round($duration, 3);

        // Log sync completion
        logger()->info(
            "PPP secrets sync completed",
            array_merge($stats, [
                "router_id" => $routerId,
                "total_mikrotik" => count($mikrotikSecrets),
            ]),
        );

        return $stats;
    }

    /**
     * Get existing secrets as a map for O(1) lookups
     * Uses select() to only fetch needed columns
     */
    protected static function getExistingSecretsMap(
        int $routerId,
        bool $forceFull,
    ): array {
        if ($forceFull) {
            return [];
        }

        return self::where("router_id", $routerId)
            ->whereNull("deleted_at")
            ->select([
                "id",
                "name",
                "mikrotik_id",
                "password",
                "service",
                "profile",
                "is_active",
                "remote_address",
                "last_synced_at",
            ])
            ->get()
            ->keyBy("name")
            ->toArray();
    }

    /**
     * Prepare records with differential sync and bulk operations
     */
    protected static function prepareRecordsOptimized(
        int $routerId,
        array $mikrotikSecrets,
        array $existingSecrets,
        $now,
    ): array {
        $records = [];
        $mikrotikIds = [];
        $passwordsToEncrypt = [];
        $passwordMap = [];

        // First pass: Validate and collect passwords for bulk encryption
        foreach ($mikrotikSecrets as $index => $secret) {
            // Validate required fields
            if (empty($secret[".id"]) || empty($secret["name"])) {
                logger()->debug("Skipping invalid secret", [
                    "index" => $index,
                    "secret" => $secret,
                ]);
                continue;
            }

            $mikrotikId = $secret[".id"];
            $name = $secret["name"];
            $mikrotikIds[] = $mikrotikId;

            // Check if record needs updating (differential sync)
            $existing = $existingSecrets[$name] ?? null;

            if ($existing && !self::needsUpdate($secret, $existing)) {
                // Skip unchanged records
                continue;
            }

            // Collect password for bulk encryption
            $password = $secret["password"] ?? "";
            if ($password && !self::isAlreadyEncrypted($password)) {
                $passwordsToEncrypt[$index] = $password;
            }

            $passwordMap[$index] = $password;
        }

        // Bulk encrypt all passwords at once (much faster than one-by-one)
        $encryptedPasswords = self::bulkEncryptPasswords($passwordsToEncrypt);

        // Second pass: Build records array
        foreach ($mikrotikSecrets as $index => $secret) {
            if (empty($secret[".id"]) || empty($secret["name"])) {
                continue;
            }

            $name = $secret["name"];
            $existing = $existingSecrets[$name] ?? null;

            if ($existing && !self::needsUpdate($secret, $existing)) {
                continue;
            }

            // Use encrypted password or original if already encrypted
            $password =
                $encryptedPasswords[$index] ??
                (self::isAlreadyEncrypted($passwordMap[$index])
                    ? $passwordMap[$index]
                    : encrypt($passwordMap[$index]));

            $records[] = [
                "router_id" => $routerId,
                "mikrotik_id" => $secret[".id"],
                "name" => $name,
                "password" => $password,
                "service" => $secret["service"] ?? "any",
                "profile" => $secret["profile"] ?? "default",
                "local_address" => $secret["local-address"] ?? null,
                "remote_address" => $secret["remote-address"] ?? null,
                "caller_id" => $secret["caller-id"] ?? null,
                "rate_limit" => $secret["rate-limit"] ?? null,
                "comment" => $secret["comment"] ?? null,
                "is_active" =>
                    !isset($secret["disabled"]) ||
                    $secret["disabled"] !== "true",
                "is_synced" => true,
                "last_synced_at" => $now,
                "updated_at" => $now,
                "created_at" => $now,
            ];
        }

        return [$records, $mikrotikIds];
    }

    /**
     * Check if record needs updating (differential sync optimization)
     */
    protected static function needsUpdate(
        array $mikrotikSecret,
        array $existing,
    ): bool {
        // Quick checks for common changes
        $isActiveChanged =
            (!isset($mikrotikSecret["disabled"]) ||
                $mikrotikSecret["disabled"] !== "true") !==
            $existing["is_active"];

        $serviceChanged =
            ($mikrotikSecret["service"] ?? "any") !== $existing["service"];
        $profileChanged =
            ($mikrotikSecret["profile"] ?? "default") !== $existing["profile"];
        $remoteAddressChanged =
            ($mikrotikSecret["remote-address"] ?? null) !==
            $existing["remote_address"];

        // Update if any field changed or if not synced in last 5 minutes
        return $isActiveChanged ||
            $serviceChanged ||
            $profileChanged ||
            $remoteAddressChanged ||
            (isset($existing["last_synced_at"]) &&
                $existing["last_synced_at"] < now()->subMinutes(5));
    }

    /**
     * Bulk encrypt passwords (faster than encrypting one-by-one)
     */
    protected static function bulkEncryptPasswords(array $passwords): array
    {
        if (empty($passwords)) {
            return [];
        }

        $encrypted = [];

        // Use array_map for better performance than foreach
        $encrypted = array_map(function ($password) {
            return encrypt($password);
        }, $passwords);

        return $encrypted;
    }

    /**
     * Check if password is already encrypted
     */
    protected static function isAlreadyEncrypted(string $password): bool
    {
        return str_starts_with($password, "eyJ");
    }

    /**
     * Perform chunked upsert for memory efficiency
     */
    protected static function performChunkedUpsert(array $records): int
    {
        $chunkSize = 500; // Optimal for most databases
        $totalSynced = 0;

        foreach (array_chunk($records, $chunkSize) as $chunk) {
            DB::table("ppp_secrets")->upsert(
                $chunk,
                ["router_id", "name"], // Unique constraint
                [
                    "mikrotik_id",
                    "password",
                    "service",
                    "profile",
                    "local_address",
                    "remote_address",
                    "caller_id",
                    "rate_limit",
                    "comment",
                    "is_active",
                    "is_synced",
                    "last_synced_at",
                    "updated_at",
                ],
            );

            $totalSynced += count($chunk);
        }

        return $totalSynced;
    }

    /**
     * Clean up orphaned secrets (optimized single query)
     */
    protected static function cleanupOrphanedSecrets(
        int $routerId,
        array $mikrotikIds,
        $now,
    ): int {
        if (empty($mikrotikIds)) {
            return 0;
        }

        return self::where("router_id", $routerId)
            ->whereNotIn("mikrotik_id", $mikrotikIds)
            ->whereNull("deleted_at")
            ->update([
                "is_synced" => false,
                "deleted_at" => $now,
            ]);
    }

    /**
     * Clear all related caches
     */
    protected static function clearRelatedCaches(int $routerId): void
    {
        $cacheKeys = [
            "ppp_stats_{$routerId}",
            "ppp_secrets_{$routerId}",
            "router_stats_{$routerId}",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    // ============================================
    // ALTERNATIVE: ASYNC SYNC (For Background Processing)
    // ============================================

    /**
     * Queue sync job for background processing
     * Use this for large routers (1000+ secrets)
     */
    public static function syncAsync(
        int $routerId,
        array $mikrotikSecrets,
    ): void {
        \Illuminate\Support\Facades\Queue::push(
            new \App\Jobs\SyncPPPSecretsJob($routerId, $mikrotikSecrets),
        );
    }

    // ============================================
    // PASSWORD ACCESSORS/MUTATORS
    // ============================================

    protected function setPasswordAttribute($value): void
    {
        if ($value && !self::isAlreadyEncrypted($value)) {
            $this->attributes["password"] = encrypt($value);
        } else {
            $this->attributes["password"] = $value;
        }
    }

    protected function getPasswordAttribute($value): ?string
    {
        try {
            return $value ? decrypt($value) : null;
        } catch (\Exception $e) {
            logger()->error("Failed to decrypt PPP password", [
                "ppp_secret_id" => $this->id,
                "error" => $e->getMessage(),
            ]);
            return null;
        }
    }

    // ============================================
    // QUERY SCOPES (Index-Optimized)
    // ============================================

    public function scopeForRouter($query, int $routerId)
    {
        return $query->where("router_id", $routerId);
    }

    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }

    public function scopeInactive($query)
    {
        return $query->where("is_active", false);
    }

    public function scopeNeedsSync($query)
    {
        return $query->where("is_synced", false);
    }

    public function scopeRecentlyConnected($query, int $hours = 24)
    {
        return $query->where(
            "last_connected_at",
            ">=",
            now()->subHours($hours),
        );
    }

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function router()
    {
        return $this->belongsTo(Router::class);
    }
}
