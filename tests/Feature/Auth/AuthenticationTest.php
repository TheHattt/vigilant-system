<?php

use App\Models\User;
use App\Models\Tenant;
use Laravel\Fortify\Features;

test("login screen can be rendered", function () {
    $response = $this->get(route("login"));

    $response->assertOk();
});

test("users can authenticate using the login screen", function () {
    $tenant = Tenant::factory()->create(["account_prefix" => "ISP001"]);
    $user = User::factory()->create([
        "tenant_id" => $tenant->id,
        "password" => bcrypt("password"),
    ]);

    $response = $this->post(route("login"), [
        "email" => $user->email,
        "password" => "password",
        "account_prefix" => "ISP001",
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route("dashboard", absolute: false));

    $this->assertAuthenticated();
});

test("users can not authenticate with invalid password", function () {
    $tenant = Tenant::factory()->create(["account_prefix" => "ISP001"]);
    $user = User::factory()->create([
        "tenant_id" => $tenant->id,
    ]);

    $response = $this->post(route("login"), [
        "email" => $user->email,
        "password" => "wrong-password",
        "account_prefix" => "ISP001",
    ]);

    $this->assertGuest();
});

test(
    "users with two factor enabled are redirected to two factor challenge",
    function () {
        if (!Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped("Two-factor authentication is not enabled.");
        }

        $tenant = Tenant::factory()->create(["account_prefix" => "ISP001"]);
        $user = User::factory()
            ->withTwoFactor()
            ->create([
                "tenant_id" => $tenant->id,
                "password" => bcrypt("password"),
            ]);

        $response = $this->post(route("login"), [
            "email" => $user->email,
            "password" => "password",
            "account_prefix" => "ISP001",
        ]);

        $response->assertRedirect(route("two-factor.login"));
        $this->assertGuest();
    },
);

test("users can logout", function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(["tenant_id" => $tenant->id]);

    $response = $this->actingAs($user)->post(route("logout"));

    $response->assertRedirect(route("home"));
    $this->assertGuest();
});
