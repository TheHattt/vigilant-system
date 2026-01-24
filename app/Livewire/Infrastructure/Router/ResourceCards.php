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

    public function mount(Router $router)
    {
        $this->router = $router;
        $this->refreshData();
    }

    public function refreshData()
    {
        // Stop polling if we are waiting for the router to come back up
        if ($this->isRebooting) {
            return;
        }

        $service = app(MikrotikService::class);
        $data = $service->getCachedData($this->router, "resources");

        if (!$data) {
            $this->hasData = false;
            return;
        }

        $currentHash = md5(json_encode($data));
        if ($currentHash === $this->lastSyncHash) {
            return;
        }

        $this->lastSyncHash = $currentHash;
        $this->hasData = true;

        $this->uptime = $data["uptime"] ?? "Unknown";
        $this->cpuLoad = (int) ($data["cpu-load"] ?? 0);

        $totalMem = $data["total-memory"] ?? 1;
        $freeMem = $data["free-memory"] ?? 0;
        $usedMem = $totalMem - $freeMem;

        $this->memory = [
            "percentage" => round(($usedMem / $totalMem) * 100),
            "used" => round($usedMem / 1024 / 1024, 1),
            "total" => round($totalMem / 1024 / 1024, 1),
        ];

        $this->temperature = $data["board-temperature"] ?? null;
        $this->isHealthy =
            $this->cpuLoad < 90 && $this->memory["percentage"] < 95;
    }

    public function triggerReboot(MikrotikService $service)
    {
        $this->isRebooting = true;
        $service->reboot($this->router);

        // Close modal via JS dispatch
        $this->dispatch("modal-close", name: "reboot-confirmation");

        session()->flash(
            "status",
            "Reboot command sent. Systems will be offline shortly.",
        );
    }

    public function render()
    {
        return view("livewire.infrastructure.router.resource-cards");
    }
}
