<?php

namespace Database\Factories;

use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Original Glaze', 'Choco Melt', 'Strawberry Dream', 'Zen Matcha',
            'Salted Caramel', 'Honey Lemon', 'Hibiscus Bloom', 'Midnight Glaze',
            'Caramel Pecan', 'Boston Cream', 'Vanilla Bean', 'Blueberry Burst',
        ]) . ' ' . fake()->numberBetween(1, 999);

        return [
            'name' => $name,
            'description' => fake()->sentence(12),
            'category' => fake()->randomElement(['classics', 'special']),
            'badge' => fake()->randomElement([null, null, null, 'best_seller', 'new']),
            'price' => fake()->numberBetween(10, 20) * 1000,
            'stock' => fake()->numberBetween(0, 60),
            'image' => null,
            'status' => fake()->randomElement(['active', 'active', 'active', 'inactive']),
        ];
    }
}
