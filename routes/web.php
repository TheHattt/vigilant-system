<?php

use App\Http\Controllers\Auth\TenantLookupController;
use Illuminate\Support\Facades\Route;

Route::get("/", function () {
    return view("welcome");
})->name("home");

Route::view("dashboard", "dashboard")
    ->middleware(["auth", "verified"])
    ->name("dashboard");

/**
 * Auth & Tenant Routes
 */
Route::get(
    "/auth/tenant-lookup/{email}",
    TenantLookupController::class,
)->middleware("throttle:tenant-lookup");

require __DIR__ . "/settings.php";
