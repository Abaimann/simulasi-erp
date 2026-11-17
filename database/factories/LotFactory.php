<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LotSourceType;
use App\Models\Company;
use App\Models\Lot;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lot>
 */
class LotFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'origin_company_id' => Company::factory(),
            'lot_number' => 'LOT-'.strtoupper(fake()->bothify('???-######')),
            'source_type' => LotSourceType::OpeningBalance,
            'manufactured_at' => now(),
            'notes' => null,
        ];
    }
}
