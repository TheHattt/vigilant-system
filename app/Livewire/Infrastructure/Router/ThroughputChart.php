<?php

namespace App\Livewire\Infrastructure\Router;

use App\Models\Router;
use App\Services\MikrotikService;
use Livewire\Component;

class ThroughputChart extends Component
{
    public Router $router;
    public $interface = "ether1";
    public $interfaces = [];

    /**
     * Listeners allow the chart to update ONLY when
     * the manual Sync button is pressed.
     */
    protected $listeners = [
        "refreshResources" => '$refresh',
        "refreshInterfaces" => '$refresh',
        "tab-changed" => '$refresh',
    ];

    public function mount(Router $router)
    {
        $this->router = $router;

        // Fetch available interfaces from the service to populate the dropdown
        $service = app(MikrotikService::class);
        $data = $service->getCachedData($router, "interfaces");

        $this->interfaces = collect($data)
            ->map(fn($iface) => $iface["name"])
            ->toArray();
    }

    /**
     * This method fetches the current traffic stats.
     * It is called by the Blade view during rendering.
     */
    public function getTraffic()
    {
        $service = app(MikrotikService::class);
        return $service->getInterfaceTraffic($this->router, $this->interface);
    }

    public function render()
    {
        return view("livewire.infrastructure.router.throughput-chart");
    }
}
