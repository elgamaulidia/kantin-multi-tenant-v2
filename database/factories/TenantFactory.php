<?php

namespace Database\Factories;

use App\Models\Canteen;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'canteen_id' => Canteen::factory(),
            'code' => strtoupper($this->faker->unique()->lexify('TNT???')),
            'slug' => $this->faker->unique()->slug(2),
            'display_name' => $this->faker->company(),
            'status' => 'active',
        ];
    }
}