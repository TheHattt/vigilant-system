<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Site;
use Illuminate\Support\Facades\Crypt;

class RouterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get a site ID
        $siteId =
            DB::table("sites")->value("id") ??
            DB::table("sites")->insertGetId([
                "name" => "Main Headquarters",
                "location" => "London, UK",
                "tenant_id" => 1,
                "created_at" => now(),
                "updated_at" => now(),
            ]);

        $nodes = [
            ["name" => "Edge-GW-01", "online" => 1],
            ["name" => "Core-Alpha", "online" => 1],
            ["name" => "London-R1", "online" => 0],
            ["name" => "Tokyo-R2", "online" => 1],
            ["name" => "NYC-DC-01", "online" => 1],
            ["name" => "Berlin-R4", "online" => 0],
            ["name" => "SG-Hub", "online" => 1],
            ["name" => "Backup-01", "online" => 1],
        ];

        foreach ($nodes as $node) {
            DB::table("routers")->insert([
                "tenant_id" => 1,
                "site_id" => $siteId,
                "name" => $node["name"],
                "hostname" => strtolower($node["name"]) . ".local", // Match Migration
                "hardware_name" => "MikroTik",
                "model" => "CCR2004",
                "api_username" => "admin",
                "api_password" => Crypt::encryptString("secret123"),
                "api_port" => "8728",
                "is_online" => $node["online"],
                "os_version" => "v7",
                "created_at" => now(),
                "updated_at" => now(),
            ]);
        }
    }
}
