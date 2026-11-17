<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CompanyBusinessType;
use App\Enums\CompanyCode;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'short_name', 'business_type', 'city', 'address', 'description', 'is_active'])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'code' => CompanyCode::class,
            'business_type' => CompanyBusinessType::class,
            'is_active' => 'boolean',
        ];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(CompanyMembership::class);
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function accessibleProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'company_product_access')
            ->withTimestamps();
    }

    public function ownedStockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class, 'owner_company_id');
    }
}
