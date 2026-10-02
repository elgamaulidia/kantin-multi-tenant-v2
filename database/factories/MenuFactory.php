<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'category_id' => MenuCategory::factory(),
            'name' => $this->faker->words(3, true),
            'base_price' => $this->faker->numberBetween(5000, 50000),
            'stock_qty' => $this->faker->numberBetween(0, 100),
            'is_available' => true,
            'prep_minutes' => $this->faker->numberBetween(5, 30),
        ];
    }
}