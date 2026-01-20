<?php

use App\Models\User;
use App\Models\Tenant;
use Laravel\Fortify\Features;

test(
    "two factor challenge redirects to login when not authenticated",
    function () {
        if (!Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped("Two-factor authentication is not enabled.");
        }

        $response = $this->get(route("two-factor.login"));

        $response->assertRedirect(route("login"));
    },
);

test("two factor challenge can be rendered", function () {
    if (!Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped("Two-factor authentication is not enabled.");
    }

    // Ensure we are in a clean state for the test
    Features::twoFactorAuthentication([
        "confirm" => true,
        "confirmPassword" => true,
    ]);

    // 1. Setup Tenant and User
    $tenant = Tenant::factory()->create(["account_prefix" => "ISP001"]);
    $user = User::factory()
        ->withTwoFactor()
        ->create([
            "tenant_id" => $tenant->id,
            "password" => bcrypt("password"),
        ]);

    // 2. Post to 'login' (not login.store) with the prefix
    $response = $this->post(route("login"), [
        "email" => $user->email,
        "password" => "password",
        "account_prefix" => "ISP001",
    ]);

    // 3. Assert the redirect to the challenge page
    $response->assertRedirect(route("two-factor.login"));

    // 4. Follow the redirect and check if it renders
    $this->get(route("two-factor.login"))->assertOk();
});
