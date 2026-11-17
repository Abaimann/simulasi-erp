<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
class WarehouseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'code' => 'GDG-'.strtoupper(fake()->bothify('???-##')),
            'name' => 'Gudang '.fake()->city(),
            'city' => fake()->city(),
            'address' => fake()->address(),
            'is_third_party_storage' => false,
            'is_active' => true,
        ];
    }
}
