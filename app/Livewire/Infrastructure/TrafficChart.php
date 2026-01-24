<?php
namespace App\Livewire\Infrastructure;

use Livewire\Component;
use App\Models\Router;
use phpseclib3\Net\SSH2;

class TrafficChart extends Component
{
    public $routerId;
    public $interface = "ether1"; // Default interface
    public $rx = 0;
    public $tx = 0;

    public function mount($routerId)
    {
        $this->routerId = $routerId;
    }

    public function fetchStats()
    {
        try {
            $router = Router::find($this->routerId);
            $ssh = new SSH2($router->host, 22, 2);

            if ($ssh->login("admin", "your_password")) {
                // Command to get interface speed once in a parseable format
                $output = $ssh->exec(
                    "/interface monitor-speed [find name=\"$this->interface\"] once",
                );

                // Regex to extract rx and tx bits-per-second
                preg_match("/rx-bits-per-second: (\d+)/", $output, $rxMatch);
                preg_match("/tx-bits-per-second: (\d+)/", $output, $txMatch);

                $this->rx = isset($rxMatch[1])
                    ? round($rxMatch[1] / 1024 / 1024, 2)
                    : 0; // Convert to Mbps
                $this->tx = isset($txMatch[1])
                    ? round($txMatch[1] / 1024 / 1024, 2)
                    : 0; // Convert to Mbps

                // Dispatch event to the frontend chart
                $this->dispatch("stats-updated", rx: $this->rx, tx: $this->tx);
            }
        } catch (\Exception $e) {
            // Handle silent fail for chart polling
        }
    }

    public function render()
    {
        return view("livewire.infrastructure.traffic-chart");
    }
}
