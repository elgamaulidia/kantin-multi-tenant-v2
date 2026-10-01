<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CanteenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->lexify('CTN???')),
            'slug' => $this->faker->unique()->slug(2),
            'name' => $this->faker->company(),
            'tax_rate' => 10.00,
            'service_fee_rate' => 5.00,
            'status' => 'active',
        ];
    }
}