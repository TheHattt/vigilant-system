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
        Schema::create("subscribers", function (Blueprint $table) {
            $table->id();
            $table->foreignId("tenant_id")->constrained()->cascadeOnDelete();
            $table->foreignId("site_id")->constrained()->cascadeOnDelete(); // subscriber belongs to site
            $table->foreignId("route_id")->constrained()->cascadeOnDelete(); // subscribed connects via this  router

            // Auth
            $table->string("username")->unique();
            $table->string("password");

            //Plan details
            $table
                ->foreignId("bandwidth_profiles_id")
                ->constrained()
                ->cascadeOnDelete();
            $table->string("static_ip");
            $table->boolean("is_active")->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("subscribers");
    }
};
