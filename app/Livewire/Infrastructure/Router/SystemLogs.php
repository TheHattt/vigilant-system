<?php

namespace App\Livewire\Infrastructure\Router;

use App\Models\Router;
use App\Models\ActivityLog;
use App\Services\MikrotikService;
use Livewire\Component;

class SystemLogs extends Component
{
    public Router $router;
    public array $logs = []; // This is what the Blade file is looking for
    public string $lastSyncHash = "";

    protected $listeners = ["refresh-all" => "refreshLogs"];

    public function mount(Router $router)
    {
        $this->router = $router;
        $this->refreshLogs();
    }

    public function refreshLogs()
    {
        $service = app(MikrotikService::class);

        // 1. Fetch Hardware Logs
        $routerLogs = $service->getCachedData($this->router, "logs") ?? [];

        // 2. Fetch App Database Logs
        // Note: Make sure you have an ActivityLog model or similar
        $dbLogs = [];
        try {
            $dbLogs = ActivityLog::where("router_id", $this->router->id)
                ->latest()
                ->limit(20)
                ->get()
                ->map(function ($log) {
                    return [
                        "time" => $log->created_at->format("H:i:s"),
                        "topics" => "internal,audit",
                        "message" => $log->description,
                        "user" => $log->user->name ?? "System",
                        "is_db" => true,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            // Fallback if the ActivityLog table doesn't exist yet
            $dbLogs = [];
        }

        // 3. Merge
        $allLogs = array_merge($routerLogs, $dbLogs);

        // 4. Sort by time descending
        usort($allLogs, function ($a, $b) {
            return strcmp($b["time"], $a["time"]);
        });

        // 5. Update the public property
        $this->logs = $allLogs;
        $this->lastSyncHash = md5(json_encode($allLogs));
    }

    public function render()
    {
        return view("livewire.infrastructure.router.system-logs");
    }
}
