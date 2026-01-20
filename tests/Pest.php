<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase".
|
*/

pest()
    ->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function () {
        // Create a default tenant for every test to prevent Foreign Key errors
        // This acts as the "Standard ISP" for your staff login tests
        Tenant::firstOrCreate(
            ["account_prefix" => "TST"], // Use 'prefix' to match your migration
            [
                "name" => "Test ISP Company",
                "slug" => "test-isp-company",
            ],
        );
    })
    ->in("Feature", "Unit");

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| The "expect()" function gives you access to a set of "expectations" methods
| that you can use to assert different things.
|
*/

expect()->extend("toBeOne", function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
|
| Expose helpers as global functions to help you reduce the number of
| lines of code in your test files.
|
*/

function actingAsStaff()
{
    $tenant = Tenant::where("account_prefix", "TST")->first();
    $user = \App\Models\User::factory()->create([
        "tenant_id" => $tenant->id,
    ]);

    return test()->actingAs($user);
}
