<?php

namespace App\Livewire\Infrastructure\Router;

use App\Models\Router;
use Livewire\Component;
use Flux\Flux;

class PppSecretCreate extends Component
{
    public Router $router;

    // Form Fields
    public $name = "";
    public $password = "";
    public $service = "pppoe";
    public $profile = "default";
    public $comment = "";

    protected $rules = [
        "name" => "required|min:3|max:64",
        "password" => "required|min:4",
        "service" => "required|in:pppoe,pptp,l2tp,sstp,ovpn,any",
        "profile" => "required",
        "comment" => "nullable|max:100",
    ];

    public function save()
    {
        $this->validate();

        try {
            // 1. Logic to send to MikroTik API would go here
            // $this->router->getApi()->post('/ppp/secret/add', [...]);

            // 2. Save to local Database
            $this->router->pppSecrets()->create([
                "name" => $this->name,
                "password" => encrypt($this->password), // Always encrypt passwords!
                "service" => $this->service,
                "profile" => $this->profile,
                "comment" => $this->comment,
                "is_active" => true,
                "is_synced" => true,
            ]);

            $this->dispatch("secret-created"); // Tell the list to refresh
            $this->reset(["name", "password", "comment"]);

            Flux::modal("create-secret")->close();
            Flux::toast("New PPP User created successfully.");
        } catch (\Exception $e) {
            session()->flash("error", "Router Error: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view("livewire.infrastructure.router.ppp-secret-create");
    }
}
