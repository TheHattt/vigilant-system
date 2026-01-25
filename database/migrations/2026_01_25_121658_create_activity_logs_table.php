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
        Schema::create("activity_logs", function (Blueprint $table) {
            $table->id();
            $table->foreignId("router_id")->constrained()->onDelete("cascade");
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->string("description"); // e.g., "Updated PPP Secret: john_doe"
            $table->string("action"); // e.g., "edit", "create", "delete"
            $table->json("changes")->nullable(); // Optional: to store old vs new values
            $table->timestamps();
        });
    } /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("activity_logs");
    }
};
