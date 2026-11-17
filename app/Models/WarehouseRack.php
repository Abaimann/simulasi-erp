<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RackZoneType;
use Database\Factories\WarehouseRackFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['warehouse_id', 'code', 'name', 'zone_type', 'is_active'])]
class WarehouseRack extends Model
{
    /** @use HasFactory<WarehouseRackFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'zone_type' => RackZoneType::class,
            'is_active' => 'boolean',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function stockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class, 'rack_id');
    }
}
