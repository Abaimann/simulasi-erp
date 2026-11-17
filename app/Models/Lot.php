<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LotSourceType;
use Database\Factories\LotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['product_id', 'origin_company_id', 'lot_number', 'source_type', 'manufactured_at', 'notes'])]
class Lot extends Model
{
    /** @use HasFactory<LotFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source_type' => LotSourceType::class,
            'manufactured_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function originCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'origin_company_id');
    }

    public function stockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeVisibleToCompany(Builder $query, Company $company): Builder
    {
        return $query->where(function (Builder $scoped) use ($company): void {
            $scoped->where('origin_company_id', $company->id)
                ->orWhereHas('stockBalances', function (Builder $balanceQuery) use ($company): void {
                    $balanceQuery->where('owner_company_id', $company->id)
                        ->orWhereHas('warehouse', fn (Builder $whQuery) => $whQuery->where('company_id', $company->id));
                })
                ->orWhereHas('stockMovements', function (Builder $moveQuery) use ($company): void {
                    $moveQuery->where('from_owner_company_id', $company->id)
                        ->orWhere('to_owner_company_id', $company->id)
                        ->orWhere('performed_company_id', $company->id)
                        ->orWhereHas('fromWarehouse', fn (Builder $whQuery) => $whQuery->where('company_id', $company->id))
                        ->orWhereHas('toWarehouse', fn (Builder $whQuery) => $whQuery->where('company_id', $company->id));
                });
        });
    }

    public function isVisibleToCompany(Company $company): bool
    {
        return static::query()
            ->whereKey($this->id)
            ->visibleToCompany($company)
            ->exists();
    }
}
