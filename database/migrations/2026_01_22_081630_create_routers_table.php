<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Tenant;
use App\Models\Site;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("routers", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignIdFor(Tenant::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignIdFor(Site::class)->constrained()->cascadeOnDelete();

            // Identity & Hardware (The fields we need!)
            $table->string("hardware_name");
            $table->string("model")->nullable(); // e.g., CCR2004, RB5009, CHR
            $table->string("serial_number")->nullable();

            //Management
            $table->string("name");
            $table->string("hostname");
            $table->string("api_port")->default("8728");
            $table->string("api_username");
            $table->string("api_password"); // encrypted

            //RADIUS AAA
            $table->string("radius_secret")->nullable();
            $table->string("radius_coa_port")->default("3799");

            // Hardware
            $table->enum("os_version", ["v6", "v7"])->default("v7");
            $table->boolean("is_online")->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("routers");
    }
};
