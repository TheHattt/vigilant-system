<?php

namespace App\Livewire\Infrastructure\Router;

use App\Models\Router;
use App\Services\MikrotikService;
use Livewire\Component;

class InterfaceTable extends Component
{
    public Router $router;
    public array $interfaces = [];
    public array $speeds = []; // Stores ['ether1' => ['rx' => 1024, 'tx' => 512]]

    public function mount(Router $router)
    {
        $this->router = $router;
        $this->refreshData();
    }

    public function refreshData()
    {
        $service = app(MikrotikService::class);

        // 1. Get General Stats (Status, Mac, Bytes)
        $this->interfaces =
            $service->getCachedData($this->router, "interfaces") ?? [];

        // 2. Get Live Traffic for each interface
        // We iterate through interfaces and fetch live monitor-traffic data
        foreach ($this->interfaces as $iface) {
            $name = $iface["name"];
            $traffic = $service->getInterfaceTraffic($this->router, $name);

            if ($traffic) {
                $this->speeds[$name] = [
                    "rx" => $this->formatSpeed(
                        $traffic["rx-bits-per-second"] ?? 0,
                    ),
                    "tx" => $this->formatSpeed(
                        $traffic["tx-bits-per-second"] ?? 0,
                    ),
                ];
            }
        }
    }

    private function formatSpeed($bps)
    {
        if ($bps >= 1000000) {
            return round($bps / 1000000, 1) . " Mbps";
        }
        if ($bps >= 1000) {
            return round($bps / 1000, 1) . " Kbps";
        }
        return $bps . " bps";
    }

    public function render()
    {
        return view("livewire.infrastructure.router.interface-table");
    }
}
