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
        Schema::create("ppp_live_sessions", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("ppp_secret_id")
                ->constrained()
                ->onDelete("cascade");
            $table->string("name"); // The username from MikroTik
            $table->string("service")->default("pppoe");
            $table->string("caller_id")->nullable(); // Usually the MAC address
            $table->string("address")->nullable(); // Remote IP
            $table->string("uptime")->nullable();
            $table->bigInteger("bytes_in")->default(0);
            $table->bigInteger("bytes_out")->default(0);
            $table->timestamps();
        });
    } /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("ppp_live_sessions");
    }
};
