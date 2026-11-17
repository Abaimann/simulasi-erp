<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StockMovementType;
use App\Enums\StockStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'movement_number',
    'idempotency_key',
    'product_id',
    'lot_id',
    'movement_type',
    'from_owner_company_id',
    'to_owner_company_id',
    'from_warehouse_id',
    'from_rack_id',
    'to_warehouse_id',
    'to_rack_id',
    'from_status',
    'to_status',
    'quantity',
    'reference_type',
    'reference_number',
    'reference_id',
    'performed_by',
    'performed_company_id',
    'occurred_at',
    'notes',
])]
class StockMovement extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'movement_type' => StockMovementType::class,
            'from_status' => StockStatus::class,
            'to_status' => StockStatus::class,
            'quantity' => 'decimal:3',
            'occurred_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    public function fromOwnerCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'from_owner_company_id');
    }

    public function toOwnerCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'to_owner_company_id');
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function fromRack(): BelongsTo
    {
        return $this->belongsTo(WarehouseRack::class, 'from_rack_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function toRack(): BelongsTo
    {
        return $this->belongsTo(WarehouseRack::class, 'to_rack_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function performedCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'performed_company_id');
    }

    public function scopeVisibleToCompany(Builder $query, Company $company): Builder
    {
        return $query->where(function (Builder $scoped) use ($company): void {
            $scoped->where('from_owner_company_id', $company->id)
                ->orWhere('to_owner_company_id', $company->id)
                ->orWhere('performed_company_id', $company->id)
                ->orWhereHas('fromWarehouse', fn (Builder $whQuery) => $whQuery->where('company_id', $company->id))
                ->orWhereHas('toWarehouse', fn (Builder $whQuery) => $whQuery->where('company_id', $company->id));
        });
    }
}
