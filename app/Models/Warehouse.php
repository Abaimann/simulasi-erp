<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WarehouseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['company_id', 'code', 'name', 'city', 'address', 'is_third_party_storage', 'is_active'])]
class Warehouse extends Model
{
    /** @use HasFactory<WarehouseFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_third_party_storage' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function racks(): HasMany
    {
        return $this->hasMany(WarehouseRack::class);
    }

    public function stockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class);
    }

    public function scopeManagedBy(Builder $query, Company $company): Builder
    {
        return $query->where('company_id', $company->id);
    }
}
