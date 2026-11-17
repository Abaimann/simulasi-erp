<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProductCategory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => 'PRD-'.strtoupper(fake()->bothify('???-###')),
            'name' => 'Kotak Karton '.fake()->word(),
            'category' => ProductCategory::FinishedGood,
            'unit' => 'kotak',
            'description' => fake()->sentence(),
            'intercompany_price' => 5000,
            'customer_selling_price' => 7000,
            'is_active' => true,
        ];
    }

    public function rawMaterial(): static
    {
        return $this->state([
            'category' => ProductCategory::RawMaterial,
            'unit' => 'kg',
            'intercompany_price' => null,
            'customer_selling_price' => null,
        ]);
    }
}
