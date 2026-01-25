<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\PppLiveSession;
use App\Models\Router;

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

    /**
     * Automatically encrypt password when set, unless already encrypted.
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value) && !str_starts_with($value, "eyJ")) {
            $this->attributes["password"] = encrypt($value);
        } else {
            $this->attributes["password"] = $value;
        }
    }

    public static function syncFromMikroTik(
        int $routerId,
        array $mikrotikSecrets,
        bool $forceFull = false,
    ): array {
        $startTime = microtime(true);
        if (empty($mikrotikSecrets)) {
            return [
                "synced" => 0,
                "created" => 0,
                "updated" => 0,
                "deleted" => 0,
                "duration" => 0,
            ];
        }

        $now = now();
        $existingSecrets = self::getExistingSecretsMap($routerId, $forceFull);
        [$records, $mikrotikIds] = self::prepareRecordsOptimized(
            $routerId,
            $mikrotikSecrets,
            $existingSecrets,
            $now,
        );

        if (!empty($records)) {
            self::performChunkedUpsert($records);
        }

        $deleted = self::cleanupOrphanedSecrets($routerId, $mikrotikIds, $now);
        self::clearRelatedCaches($routerId);

        return [
            "synced" => count($records),
            "deleted" => $deleted,
            "duration" => round(microtime(true) - $startTime, 3),
        ];
    }

    protected static function getExistingSecretsMap(
        int $routerId,
        bool $forceFull,
    ): array {
        if ($forceFull) {
            return [];
        }
        return self::where("router_id", $routerId)
            ->whereNull("deleted_at")
            ->get()
            ->keyBy("name")
            ->toArray();
    }

    protected static function prepareRecordsOptimized(
        int $routerId,
        array $mikrotikSecrets,
        array $existingSecrets,
        $now,
    ): array {
        $records = [];
        $mikrotikIds = [];

        foreach ($mikrotikSecrets as $secret) {
            if (empty($secret[".id"]) || empty($secret["name"])) {
                continue;
            }

            $mikrotikIds[] = $secret[".id"];
            $name = $secret["name"];
            $existing = $existingSecrets[$name] ?? null;

            if ($existing && !self::needsUpdate($secret, $existing)) {
                continue;
            }

            $records[] = [
                "router_id" => $routerId,
                "mikrotik_id" => $secret[".id"],
                "name" => $name,
                "password" => str_starts_with($secret["password"] ?? "", "eyJ")
                    ? $secret["password"]
                    : encrypt($secret["password"] ?? ""),
                "service" => $secret["service"] ?? "any",
                "profile" => $secret["profile"] ?? "default",
                "comment" => $secret["comment"] ?? null,
                "is_active" =>
                    !isset($secret["disabled"]) ||
                    $secret["disabled"] !== "true",
                "is_synced" => true,
                "last_synced_at" => $now,
                "updated_at" => $now,
                "created_at" => $existing["created_at"] ?? $now,
            ];
        }
        return [$records, $mikrotikIds];
    }

    protected static function needsUpdate(array $m, array $e): bool
    {
        return ($m["service"] ?? "any") !== $e["service"] ||
            ($m["profile"] ?? "default") !== $e["profile"] ||
            (!isset($m["disabled"]) || $m["disabled"] !== "true") !==
                $e["is_active"] ||
            ($e["last_synced_at"] ?? null) < now()->subMinutes(10);
    }

    protected static function performChunkedUpsert(array $records): void
    {
        foreach (array_chunk($records, 500) as $chunk) {
            DB::table("ppp_secrets")->upsert(
                $chunk,
                ["router_id", "name"],
                [
                    "mikrotik_id",
                    "password",
                    "service",
                    "profile",
                    "comment",
                    "is_active",
                    "is_synced",
                    "last_synced_at",
                    "updated_at",
                ],
            );
        }
    }

    protected static function cleanupOrphanedSecrets(
        int $routerId,
        array $mikrotikIds,
        $now,
    ): int {
        return self::where("router_id", $routerId)
            ->whereNotIn("mikrotik_id", $mikrotikIds)
            ->where("last_synced_at", "<", $now->subSeconds(30))
            ->delete();
    }
    public function liveSessions(): HasMany
    {
        return $this->hasMany(PppLiveSession::class);
    }
    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }
    protected static function clearRelatedCaches(int $routerId): void
    {
        Cache::forget("ppp_stats_{$routerId}");
    }
}
