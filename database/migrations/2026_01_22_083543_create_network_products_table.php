<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //Ip Pools(Linked to a Router)
        Schema::create("ip_pools", function (Blueprint $table) {
            $table->id();
            $table->foreignId("tenant_id")->constrained()->cascadeOnDelete();
            $table->foreignId("router_id")->constrained()->cascadeOnDelete();
            $table->string("name");
            $table->string("range");
            $table->timestamps();
        });

        //Bandwidth Profiles(Linked to a Tenant)
        Schema::create("bandwidth_profiles", function (Blueprint $table) {
            $table->id();
            $table->foreignId("tenant_id")->constrained()->cascadeOnDelete();
            $table->string("name");
            $table->integer("download_kbps");
            $table->integer("upload_kbps");
            $table->string("mikrotik_rate_limit");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("ip_pools");
        Schema::dropIfExists("bandwidth_profiles");
    }
};
