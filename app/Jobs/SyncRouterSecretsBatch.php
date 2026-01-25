<?php

namespace App\Jobs;

use App\Models\Router;
use App\Models\PppSecret;
use App\Services\MikrotikService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncRouterSecretsBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $routerId,
        public int $batchSize,
        public int $offset,
    ) {}

    public function handle(MikrotikService $service): void
    {
        $router = Router::find($this->routerId);
        if (!$router || !$service->testConnection($router)) {
            return; // Fail silently or let the next retry handle it
        }

        // Fetch the specific batch of unsynced secrets
        $secrets = PppSecret::where("router_id", $this->routerId)
            ->where("is_synced", false)
            ->skip($this->offset)
            ->take($this->batchSize)
            ->get();

        foreach ($secrets as $secret) {
            try {
                if ($secret->mikrotik_id) {
                    // Scenario: Update existing MikroTik entry
                    $service->updatePPPSecret($router, $secret, [
                        "name" => $secret->name,
                        "password" => $secret->password,
                        "service" => $secret->service,
                        "profile" => $secret->profile,
                        "comment" => $secret->comment,
                        "disabled" => $secret->is_active ? "no" : "yes",
                    ]);
                } else {
                    // Scenario: Create new entry and save the returned .id
                    $response = $service->createPPPSecret($router, [
                        "name" => $secret->name,
                        "password" => $secret->password,
                        "service" => $secret->service,
                        "profile" => $secret->profile,
                        "comment" => $secret->comment,
                    ]);

                    if (isset($response[0][".id"])) {
                        $secret->mikrotik_id = $response[0][".id"];
                    }
                }

                $secret->is_synced = true;
                $secret->save();
            } catch (\Exception $e) {
                Log::error(
                    "Failed to sync secret ID {$secret->id} in batch: " .
                        $e->getMessage(),
                );
                // We keep is_synced = false so it can be retried in the next manual sync
            }
        }
    }
}
