<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Validation\ValidationException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        /**
         * MikroTik Date Macro
         * Handles "never" strings and MikroTik's specific date formatting (jan/24/2026).
         */
        Carbon::macro("mikrotikDiff", function ($date) {
            if (!$date || $date === "never") {
                return "Never";
            }

            try {
                // Replace slashes with hyphens to ensure Carbon parses the date correctly
                $normalized = str_replace("/", "-", $date);
                return Carbon::parse($normalized)->diffForHumans();
            } catch (\Exception $e) {
                return "Invalid Date";
            }
        });

        Gate::define(
            "tenant",
            fn($user) => $user->is_super_admin ? true : null,
        );

        /**
         * Instant lookup protection
         * Throttles the real-time prefix detection API.
         * Limited to 4 attempts to prevent bulk email "fishing" while allowing for typos.
         */
        RateLimiter::for(
            "tenant-lookup",
            fn(Request $request) => [
                Limit::perMinutes(2, 4)->by($request->ip()),
                Limit::perMinutes(2, 4)->by(
                    strtolower($request->route("email") ?? $request->ip()),
                ),
            ],
        );

        /**
         * Fortify Login protection - The "Sticky" Bouncer
         * Set to 3 attempts to align with the "3 Strikes" UI.
         */
        RateLimiter::for("login", function (Request $request) {
            $email = strtolower((string) $request->email);
            $key = "auth_lockout_" . $email . $request->ip();

            return Limit::perMinute(3)
                ->by($key)
                ->response(function (Request $request, array $headers) {
                    $seconds = $headers["Retry-After"] ?? 30;

                    throw ValidationException::withMessages([
                        "email" => [
                            __("auth.throttle", ["seconds" => $seconds]),
                        ],
                    ]);
                });
        });

        /**
         * Password Reset protection
         */
        RateLimiter::for(
            "password.reset",
            fn(Request $request) => Limit::perMinute(3)->by($request->ip()),
        );
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(app()->isProduction());

        Password::defaults(
            fn(): ?Password => app()->isProduction()
                ? Password::min(12)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
                : null,
        );
    }
}
