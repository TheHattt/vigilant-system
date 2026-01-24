<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MikrotikService;

class TestMikrotikConnection extends Command
{
    protected $signature = "mikrotik:test {host} {user} {pass} {port=8728}";
    protected $description = "Test the API connection and Radius provisioning to a MikroTik";

    public function handle(MikrotikService $service)
    {
        $this->info("Attempting to connect to {$this->argument("host")}...");

        $success = $service->verifyAndProvision([
            "host" => $this->argument("host"),
            "api_username" => $this->argument("user"),
            "api_password" => $this->argument("pass"),
            "port" => (int) $this->argument("port"),
            "radius_secret" => "test_secret_123",
        ]);

        if ($success) {
            $this->success("Successfully connected and provisioned Radius!");
        } else {
            $this->error(
                "Failed to connect. Check IP, Port, and API service status.",
            );
        }
    }
}
