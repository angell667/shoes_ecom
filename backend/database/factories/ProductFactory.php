<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => \App\Models\Category::factory(),
            'name' => $this->faker->unique()->words(3, true),
            'slug' => \Illuminate\Support\Str::slug($this->faker->unique()->words(3, true)),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 50, 300),
            'sale_price' => null,
            'image' => $this->faker->imageUrl(600, 600, 'product'),
            'images' => null,
            'brand' => $this->faker->randomElement(['Nike', 'Adidas', 'Puma', 'New Balance', 'Asics']),
            'sku' => 'SKU-' . strtoupper($this->faker->unique()->bothify('##??##')),
            'stock' => $this->faker->numberBetween(0, 100),
            'is_featured' => $this->faker->boolean(20),
            'is_active' => true,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function onSale(): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_price' => $this->faker->randomFloat(2, $attributes['price'] * 0.5, $attributes['price'] * 0.9),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $this->faker->numberBetween(1, 5),
        ]);
    }
}
