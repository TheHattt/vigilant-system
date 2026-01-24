<?php

namespace App\Livewire\Infrastructure\Router;

use App\Models\Router;
use App\Services\MikrotikService;
use Livewire\Component;

class SystemLogs extends Component
{
    public Router $router;
    public array $logs = [];
    public string $lastSyncHash = "";

    // Added listener to react to "Sync All" button
    protected $listeners = ["refresh-all" => "refreshLogs"];

    public function mount(Router $router)
    {
        $this->router = $router;
        $this->refreshLogs();
    }

    public function refreshLogs()
    {
        $service = app(MikrotikService::class);
        $newLogs = $service->getCachedData($this->router, "logs");

        if (!$newLogs) {
            return;
        }

        $currentHash = md5(json_encode($newLogs));
        if ($currentHash !== $this->lastSyncHash) {
            $this->logs = array_reverse($newLogs); // Latest logs first
            $this->lastSyncHash = $currentHash;
        }
    }

    public function render()
    {
        return view("livewire.infrastructure.router.system-logs");
    }
}
