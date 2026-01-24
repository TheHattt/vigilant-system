<?php

namespace App\Livewire\Infrastructure\Router;

use App\Models\Router;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class SettingsForm extends Component
{
    public Router $router;
    public $name;
    public $host;
    public $api_port;

    public function mount(Router $router)
    {
        $this->router = $router;
        $this->name = $router->name;
        $this->host = $router->host;
        $this->api_port = $router->api_port;
    }

    public function save()
    {
        // Auth check before saving
        if ($this->router->tenant_id !== Auth::user()->tenant_id) {
            return;
        }

        $validated = $this->validate([
            "name" => "required|string|max:255",
            "host" => "required|string", // Removed 'ip' filter in case you use hostnames
            "api_port" => "required|numeric",
        ]);

        $this->router->update($validated);

        $this->dispatch("refresh-all");

        // Close the flux modal by name
        $this->js("\$flux.modal('edit-router').hide()");
    }

    public function delete()
    {
        if ($this->router->tenant_id !== Auth::user()->tenant_id) {
            return;
        }

        $this->router->delete();
        return redirect()->route("router.index");
    }

    public function render()
    {
        return view("livewire.infrastructure.router.settings-form");
    }
}
