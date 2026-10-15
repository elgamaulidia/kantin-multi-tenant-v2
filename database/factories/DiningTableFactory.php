<?php

namespace Database\Factories;

use App\Models\Canteen;
use App\Models\DiningTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DiningTable> */
class DiningTableFactory extends Factory
{
    protected $model = DiningTable::class;

    public function definition(): array
    {
        return [
            'canteen_id' => Canteen::factory(),
            'code' => 'M'.fake()->unique()->numerify('####'),
            'label' => 'Meja '.fake()->numberBetween(1, 99),
            'zone' => fake()->randomElement(['Indoor', 'Outdoor']),
            'status' => 'active',
        ];
    }
}
