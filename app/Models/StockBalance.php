<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StockStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'bucket_key',
    'product_id',
    'lot_id',
    'owner_company_id',
    'warehouse_id',
    'rack_id',
    'status',
    'quantity',
])]
class StockBalance extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StockStatus::class,
            'quantity' => 'decimal:3',
        ];
    }

    public static function buildBucketKey(
        int $productId,
        int $lotId,
        int $ownerCompanyId,
        ?int $warehouseId,
        ?int $rackId,
        StockStatus $status
    ): string {
        return sprintf(
            'p:%d|l:%d|o:%d|w:%d|r:%d|s:%s',
            $productId,
            $lotId,
            $ownerCompanyId,
            $warehouseId ?? 0,
            $rackId ?? 0,
            $status->value
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    public function ownerCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'owner_company_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(WarehouseRack::class, 'rack_id');
    }

    public function scopeVisibleToCompany(Builder $query, Company $company): Builder
    {
        return $query->where(function (Builder $scoped) use ($company): void {
            $scoped->where('owner_company_id', $company->id)
                ->orWhereHas('warehouse', fn (Builder $whQuery) => $whQuery->where('company_id', $company->id));
        });
    }

    public function scopePositive(Builder $query): Builder
    {
        return $query->where('quantity', '>', 0);
    }
}
