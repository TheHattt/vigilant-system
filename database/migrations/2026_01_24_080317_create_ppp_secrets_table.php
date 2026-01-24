<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("ppp_secrets", function (Blueprint $table) {
            // Primary Key
            $table->id();

            // Foreign Keys with proper indexing
            $table->foreignId("router_id")->constrained()->onDelete("cascade");

            // MikroTik Reference - optimized length
            $table
                ->string("mikrotik_id", 50)
                ->index()
                ->comment("MikroTik internal ID (*A)");

            // User Credentials - optimized lengths and security
            $table->string("name", 100)->comment("PPP username");
            $table->text("password")->comment("Encrypted password"); // Use encryption

            // Service Configuration
            $table
                ->string("service", 20)
                ->default("any")
                ->index()
                ->comment("pppoe, pptp, l2tp, sstp, ovpn");
            $table
                ->string("profile", 100)
                ->index()
                ->comment("MikroTik profile name");

            // Network Configuration
            $table
                ->string("local_address", 45)
                ->nullable()
                ->comment("Server IP (IPv4/IPv6)");
            $table
                ->string("remote_address", 45)
                ->nullable()
                ->comment("Client IP (IPv4/IPv6)");
            $table
                ->string("caller_id", 100)
                ->nullable()
                ->index()
                ->comment("MAC address or phone number");

            // Rate Limiting
            $table
                ->string("rate_limit", 50)
                ->nullable()
                ->comment("rx/tx rates");

            // Routes (if static routes needed)
            $table
                ->text("routes")
                ->nullable()
                ->comment("JSON array of static routes");

            // Status Management
            $table
                ->boolean("is_active")
                ->default(true)
                ->index()
                ->comment("Enable/disable without deletion");
            $table
                ->boolean("is_synced")
                ->default(false)
                ->index()
                ->comment("Sync status with MikroTik");

            // Metadata
            $table->text("comment")->nullable()->comment("Admin notes");

            // Sync Tracking - CRITICAL for performance
            $table
                ->timestamp("last_synced_at")
                ->nullable()
                ->index()
                ->comment("Last successful sync");
            $table
                ->timestamp("last_connected_at")
                ->nullable()
                ->index()
                ->comment("Last user connection");

            // Soft Deletes - Keep history
            $table->softDeletes();

            // Standard timestamps
            $table->timestamps();

            // Composite Indexes for Common Queries
            $table->unique(["router_id", "name"], "unique_router_username");
            $table->index(["router_id", "is_active"], "idx_router_active");
            $table->index(["router_id", "last_synced_at"], "idx_router_sync");
            $table->index(["is_active", "last_synced_at"], "idx_active_sync");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("ppp_secrets");
    }
};
