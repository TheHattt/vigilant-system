<?php

namespace App\Actions\Fortify;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // Add this!
use Illuminate\Support\Facades\Validator; // Add this!
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * Validate and create a newly registered user.
     */
    public function create(array $input): User
    {
        // 1. Validation is mandatory here for Fortify to work
        Validator::make($input, [
            "name" => ["required", "string", "max:80"],
            "email" => [
                "required",
                "string",
                "email",
                "max:100",
                "unique:users",
            ],
            "password" => ["required", "string", "min:8", "confirmed"],
            "company_name" => ["required", "string", "max:80"],
            "prefix" => [
                "nullable",
                "string",
                "max:10",
                "unique:tenants,prefix",
            ],
        ])->validate();

        return DB::transaction(function () use ($input) {
            // 2. Create the Tenant
            $tenant = Tenant::create([
                "name" => $input["company_name"],
                "slug" => Str::slug($input["company_name"]),
                "prefix" => $input["prefix"] ?? strtoupper(Str::random(3)),
            ]);

            // 3. Create the User linked to Tenant
            return User::create([
                "name" => $input["name"],
                "email" => $input["email"],
                "password" => Hash::make($input["password"]),
                "tenant_id" => $tenant->id,
                "is_super_admin" => false,
            ]);
        });
    }
}
