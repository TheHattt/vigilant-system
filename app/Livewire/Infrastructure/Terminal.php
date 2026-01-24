<?php

namespace App\Livewire\Infrastructure;

use Livewire\Component;
use App\Models\Router;
use Livewire\Attributes\Locked;
use phpseclib3\Net\SSH2;

class Terminal extends Component
{
    #[Locked]
    public ?int $routerId = null;

    public string $command = "";
    public array $history = [];
    public string $connectionStatus = "stable";

    public array $suggestions = [
        "System" => [
            "Resources" => "/system resource print",
            "Health" => "/system health print",
            "Identity" => "/system identity print",
        ],
        "Network" => [
            "Interfaces" => "/interface print",
            "IP Addresses" => "/ip address print",
            "Routes" => "/ip route print",
        ],
    ];

    public function mount($routerId = null)
    {
        $this->routerId = $routerId;
        $this->checkConnection();

        $this->history[] = [
            "type" => "system",
            "line" => "Secure Shell Session Initialized.",
        ];
    }

    public function checkConnection()
    {
        $router = Router::find($this->routerId);
        if (!$router) {
            return;
        }

        $connection = @fsockopen($router->host, 22, $errno, $errstr, 1);

        if ($connection) {
            $this->connectionStatus = "stable";
            fclose($connection);
        } else {
            $this->connectionStatus = "disconnected";
        }
    }

    public function selectCommand($cmd)
    {
        $this->command = $cmd;
    }

    public function clearHistory()
    {
        $this->history = [["type" => "system", "line" => "Buffer cleared."]];
    }

    public function runCommand()
    {
        if (empty(trim($this->command))) {
            return;
        }

        $currentCommand = $this->command;
        $this->history[] = ["type" => "input", "line" => $currentCommand];
        $this->command = "";

        try {
            $router = Router::find($this->routerId);
            $ssh = new SSH2($router->host, 22, 5);

            if (!$ssh->login("admin", "your_password")) {
                throw new \Exception("Auth Failed");
            }

            $output = $ssh->exec($currentCommand);
            $this->history[] = [
                "type" => "output",
                "line" => $output ?: "Success.",
            ];
        } catch (\Exception $e) {
            $this->history[] = [
                "type" => "system",
                "line" => "Error: " . $e->getMessage(),
            ];
        }

        $this->dispatch("terminal-output-updated");
    }

    public function render()
    {
        return view("livewire.infrastructure.terminal", [
            "router" => Router::find($this->routerId),
        ]);
    }
}
