<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use App\Models\User;
use App\Models\Tenant;
use App\Http\Response\LoginResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            LoginResponse::class,
        );
    }
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();

        /**
         * Custom Authentication Logic
         * Handles both Global Super Admins and Scoped Tenant/ISP Users.
         */
        Fortify::authenticateUsing(function (Request $request) {
            $email = $request->email;
            $password = $request->password;
            $prefix = $request->account_prefix;

            // 1. Initial User Lookup
            $user = User::where("email", $email)->first();

            if (!$user) {
                return null;
            }

            // 2. Handle Super Admin Login (Platform Owner)
            // Super Admins bypass tenant-specific scoping.
            if ($user->is_super_admin) {
                if (Hash::check($password, $user->password)) {
                    return $user;
                }
            }

            // 3. Handle Tenant/ISP User Login
            // We find the tenant using the prefix/slug sent from the frontend.
            if ($prefix) {
                $tenant = Tenant::where("slug", $prefix)
                    ->orWhere("account_prefix", $prefix)
                    ->first();

                if ($tenant) {
                    // Re-verify the user belongs to THIS specific tenant.
                    // This prevents cross-tenant credential stuffing.
                    $tenantUser = User::where("email", $email)
                        ->where("tenant_id", $tenant->id)
                        ->first();

                    if (
                        $tenantUser &&
                        Hash::check($password, $tenantUser->password)
                    ) {
                        return $tenantUser;
                    }
                }
            }

            return null;
        });
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn() => view("livewire.auth.login"));
        Fortify::verifyEmailView(fn() => view("livewire.auth.verify-email"));
        Fortify::twoFactorChallengeView(
            fn() => view("livewire.auth.two-factor-challenge"),
        );
        Fortify::confirmPasswordView(
            fn() => view("livewire.auth.confirm-password"),
        );
        Fortify::registerView(fn() => view("livewire.auth.register"));
        Fortify::resetPasswordView(
            fn() => view("livewire.auth.reset-password"),
        );
        Fortify::requestPasswordResetLinkView(
            fn() => view("livewire.auth.forgot-password"),
        );
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for("two-factor", function (Request $request) {
            return Limit::perMinute(5)->by(
                $request->session()->get("login.id"),
            );
        });

        RateLimiter::for("login", function (Request $request) {
            $throttleKey = Str::transliterate(
                Str::lower($request->input(Fortify::username())) .
                    "|" .
                    $request->ip(),
            );

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
