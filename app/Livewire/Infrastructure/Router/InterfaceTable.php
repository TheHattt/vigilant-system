<?php

namespace App\Livewire\Infrastructure\Router;

use App\Models\Router;
use App\Services\MikrotikService;
use Livewire\Component;

class InterfaceTable extends Component
{
    public Router $router;
    public array $interfaces = [];
    public ?string $lastSync = null;
    public string $lastSyncHash = "";

    // Modal & Action State
    public ?string $selectedInterfaceId = null;
    public ?string $selectedInterfaceName = null;
    public bool $isDisabling = false;

    public function mount(Router $router)
    {
        $this->router = $router;
        $this->loadData();
    }

    /**
     * Polled refresh: Only updates state if hardware data has actually changed.
     */
    public function refreshData(MikrotikService $service)
    {
        $newData = $service->getCachedData($this->router, "interfaces");
        if (!$newData) {
            return;
        }

        $newDataHash = md5(json_encode($newData));

        // If nothing changed on the router, don't trigger a Livewire re-render
        if ($newDataHash === $this->lastSyncHash) {
            return;
        }

        $this->interfaces = $newData;
        $this->lastSyncHash = $newDataHash;
        $this->lastSync = now()->format("H:i:s");
    }

    /**
     * Manual/Force load from hardware.
     */
    public function loadData()
    {
        $service = app(MikrotikService::class);
        $this->interfaces =
            $service->getCachedData($this->router, "interfaces") ?? [];
        $this->lastSyncHash = md5(json_encode($this->interfaces));
        $this->lastSync = now()->format("H:i:s");
    }

    /**
     * Prepares the modal state.
     */
    public function confirmToggle($id, $name, $currentDisabledStatus)
    {
        $this->selectedInterfaceId = $id;
        $this->selectedInterfaceName = $name;

        // If disabled is 'false', we are about to disable it.
        $this->isDisabling = $currentDisabledStatus === "false";

        $this->dispatch("modal-show", name: "interface-toggle-modal");
    }

    /**
     * Executes the MikroTik API command.
     */
    public function toggleInterface(MikrotikService $service)
    {
        $success = $service->setInterfaceStatus(
            $this->router,
            $this->selectedInterfaceId,
            $this->isDisabling,
        );

        if ($success) {
            $this->dispatch("modal-close", name: "interface-toggle-modal");

            // Give the router a half-second to commit before we re-read
            usleep(500000);
            $this->loadData();
        } else {
            // Error handling could go here (e.g., Flux::toast)
        }
    }

    public function render()
    {
        return view("livewire.infrastructure.router.interface-table");
    }
}
