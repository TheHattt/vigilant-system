<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TenantFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            "name" => $name,
            "slug" => Str::slug($name), // Converts "Weimann Ltd" to "weimann-ltd"
            "account_prefix" => strtoupper(
                $this->faker->unique()->lexify("????"),
            ),
        ];
    }
}
