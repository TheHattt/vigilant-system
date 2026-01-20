<?php

use App\Models\User;
use App\Models\Tenant;

test("registration screen can be rendered", function () {
    $response = $this->get("/register");

    $response->assertStatus(200);
});

test("new users can register with company details", function () {
    $response = $this->post("/register", [
        "name" => "Test User",
        "email" => "test@example.com",
        "company_name" => "Fast Net ISP", // Required by our Action
        "account_prefix" => "FNT", // Required by our Action
        "password" => "password",
        "password_confirmation" => "password",
    ]);

    $this->assertAuthenticated();

    // Verify user is linked to the correct tenant
    $user = User::where("email", "test@example.com")->first();
    $tenant = Tenant::where("account_prefix", "FNT")->first();

    expect($user->tenant_id)->toBe($tenant->id);

    $response->assertRedirect(route("dashboard", absolute: false));
});
