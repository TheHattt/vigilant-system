<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\MikrotikService;

class SyncUnsyncedPppSecrets implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(MikrotikService $service): void
    {
        // Find all secrets that haven't been pushed to hardware yet
        $pending = \App\Models\PppSecret::where("is_synced", false)->get();

        foreach ($pending as $secret) {
            try {
                // Attempt to push to the specific router assigned to this secret
                $response = $service->createPPPSecret($secret->router, [
                    "name" => $secret->name,
                    "password" => $secret->password,
                    "service" => $secret->service,
                    "profile" => $secret->profile,
                    "comment" => $secret->comment,
                ]);

                $mId = $response[0][".id"] ?? ($response[".id"] ?? null);

                $secret->update([
                    "mikrotik_id" => $mId,
                    "is_synced" => true,
                ]);

                Log::info(
                    "Auto-Sync Success: User {$secret->name} pushed to {$secret->router->name}",
                );
            } catch (\Exception $e) {
                // Router still offline? Just log and skip to next; we'll try again next minute.
                Log::warning(
                    "Auto-Sync Retry failed for {$secret->name}: " .
                        $e->getMessage(),
                );
            }
        }
    }
}
