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

class FortifyServiceProvider extends ServiceProvider
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
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();

        // Authenticate user
        Fortify::authenticateUsing(function (Request $request) {
            // find tenant first
            $tenant = Tenant::where(
                "account_prefix",
                $request->account_prefix,
            )->first();

            // see if user exists in tenant's table
            if ($tenant) {
                $user = User::where("email", $request->email)
                    ->where("tenant_id", $tenant->id)
                    ->first();
                if ($user && Hash::check($request->password, $user->password)) {
                    return $user;
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
