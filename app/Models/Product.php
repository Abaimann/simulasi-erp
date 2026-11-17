<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductCategory;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'sku',
    'name',
    'category',
    'unit',
    'description',
    'intercompany_price',
    'customer_selling_price',
    'is_active',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => ProductCategory::class,
            'intercompany_price' => 'integer',
            'customer_selling_price' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function accessibleCompanies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_product_access')
            ->withTimestamps();
    }

    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class);
    }

    public function stockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class);
    }

    public function scopeAccessibleByCompany(Builder $query, Company $company): Builder
    {
        return $query->whereHas('accessibleCompanies', function (Builder $subQuery) use ($company): void {
            $subQuery->where('companies.id', $company->id);
        });
    }

    public function isAccessibleByCompany(Company $company): bool
    {
        return $this->accessibleCompanies()
            ->where('companies.id', $company->id)
            ->exists();
    }
}
