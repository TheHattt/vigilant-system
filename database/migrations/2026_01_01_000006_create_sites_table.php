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
        Schema::create("sites", function (Blueprint $table) {
            $table->id();
            $table->foreignId("tenant_id")->constrained()->cascadeOnDelete();
            $table->string("name"); // e.g. "Main Site or West Side Tower"
            $table->string("location"); // e.g. "123 Main St"
            $table->string("contact_info")->nullable(); // e.g. "123 Main St"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("sites");
    }
};
