<?php

use App\Http\Controllers\Auth\TenantLookupController;
use App\Http\Controllers\Auth\OnboardingController;
use App\Http\Controllers\RouterController;
use App\Livewire\Infrastructure\Router\PppSecrets;
use Illuminate\Support\Facades\Route;

Route::get("/", function () {
    return view("welcome");
})->name("home");

Route::prefix("auth")
    ->name("auth.")
    ->group(function () {
        Route::get("tenant-lookup/{email}", TenantLookupController::class)
            ->middleware("throttle:tenant-lookup")
            ->name("tenant-lookup");
    });

Route::middleware(["auth", "verified"])->group(function () {
    Route::view("dashboard", "dashboard")->name("dashboard");

    Route::prefix("onboarding")
        ->name("onboarding.")
        ->group(function () {
            Route::get("mikrotik", function () {
                return view("auth.onboarding");
            })->name("mikrotik");

            Route::post("mikrotik/save", [
                OnboardingController::class,
                "store",
            ])->name("save");
        });

    Route::prefix("routers")
        ->name("router.")
        ->group(function () {
            Route::get("/", [RouterController::class, "index"])->name("index");

            Route::get("{router}", [RouterController::class, "show"])->name(
                "show",
            );

            Route::get("{router}/export", [
                RouterController::class,
                "export",
            ])->name("export");

            /*
            |--------------------------------------------------------------------------
            | PPP Secrets Routes (Nested Resource)
            |--------------------------------------------------------------------------
            */
            Route::prefix("{router}/ppp-secrets")
                ->name("ppp.")
                ->group(function () {
                    // FIXED: Added the route definition here
                    Route::get("/", PppSecrets::class)->name("index");
                });
        });

    require __DIR__ . "/settings.php";
});
