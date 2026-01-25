<?php

namespace App\Livewire\Infrastructure\Router;

use App\Models\Router;
use App\Services\MikrotikService;
use Livewire\Component;

class ResourceCards extends Component
{
    public Router $router;
    public bool $hasData = false;
    public bool $isHealthy = true;
    public bool $isRebooting = false;

    public string $uptime = "00:00:00";
    public int $cpuLoad = 0;
    public array $memory = ["percentage" => 0, "used" => 0, "total" => 0];
    public ?int $temperature = null;
    public string $lastSyncHash = "";

    /**
     * Listeners allow this component to update ONLY when
     * the manual Sync button is pressed.
     */
    protected $listeners = [
        "refreshResources" => "refreshData",
        "tab-changed" => "refreshData",
    ];

    public function mount(Router $router)
    {
        $this->router = $router;
        $this->refreshData();
    }

    /**
     * This method now only runs when called by mount()
     * or triggered by the 'refreshResources' event.
     */
    public function refreshData()
    {
        if ($this->isRebooting) {
            return;
        }

        $service = app(MikrotikService::class);
        $data = $service->getCachedData($this->router, "resources");

        if (!$data) {
            $this->hasData = false;
            $this->isHealthy = false;
            return;
        }

        $currentHash = md5(json_encode($data));
        if ($currentHash === $this->lastSyncHash) {
            $this->hasData = true;
            return;
        }

        $this->lastSyncHash = $currentHash;
        $this->hasData = true;

        // Map Mikrotik API keys to class properties
        $this->uptime = $data["uptime"] ?? "00:00:00";
        $this->cpuLoad = (int) ($data["cpu-load"] ?? 0);

        $totalMem = (int) ($data["total-memory"] ?? 1);
        $freeMem = (int) ($data["free-memory"] ?? 0);
        $usedMem = $totalMem - $freeMem;

        $this->memory = [
            "percentage" => (int) round(($usedMem / $totalMem) * 100),
            "used" => round($usedMem / 1024 / 1024, 1),
            "total" => round($totalMem / 1024 / 1024, 1),
        ];

        $this->temperature = $data["board-temperature"] ?? null;

        // Health thresholds
        $this->isHealthy =
            $this->cpuLoad < 90 && $this->memory["percentage"] < 95;
    }

    public function triggerReboot(MikrotikService $service)
    {
        $this->isRebooting = true;
        $service->reboot($this->router);

        $this->dispatch("modal-close", name: "reboot-confirmation");

        session()->flash("status", "Reboot command sent.");
    }

    public function render()
    {
        // Removed the refreshData() call from here to stop auto-execution on every UI interaction.
        return view("livewire.infrastructure.router.resource-cards");
    }
}
