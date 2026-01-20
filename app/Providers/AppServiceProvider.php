<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request; // Changed from Facade to Type-hint for the closure
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

        Gate::define("tenant", function ($user) {
            return $user->is_super_admin ? true : null;
        });

        // Instant lookup protection
        RateLimiter::for("tenant-lookup", function (Request $request) {
            return [
                Limit::perMinutes(2, 5)->by($request->ip()),
                Limit::perMinutes(2, 3)->by(
                    strtolower($request->route("email") ?? $request->ip()),
                ),
            ];
        });

        //
        // Fortify Login protection - Prevents the ugly 429 page
        RateLimiter::for("login", function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(5)
                ->by($email . $request->ip())
                ->response(function (Request $request, array $headers) {
                    $seconds = $headers["Retry-After"] ?? 60;

                    throw ValidationException::withMessages([
                        "email" => [
                            __("auth.throttle", ["seconds" => $seconds]),
                        ],
                    ]);
                });
        });
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
