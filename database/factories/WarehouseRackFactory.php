<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RackZoneType;
use App\Models\Warehouse;
use App\Models\WarehouseRack;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WarehouseRack>
 */
class WarehouseRackFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'code' => 'RAK-'.strtoupper(fake()->bothify('?-##')),
            'name' => 'Rak Penyimpanan '.fake()->numerify('##'),
            'zone_type' => RackZoneType::Storage,
            'is_active' => true,
        ];
    }
}
