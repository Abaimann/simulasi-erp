<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\StockStatus;
use App\Models\Lot;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\ActiveCompanyContext;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function index(ActiveCompanyContext $companyContext): View
    {
        $activeCompany = $companyContext->companyOrFail();

        $ownedBalances = StockBalance::query()
            ->with(['product', 'warehouse.company', 'rack'])
            ->where('owner_company_id', $activeCompany->id)
            ->positive()
            ->get();

        $custodialBalances = StockBalance::query()
            ->with(['product', 'ownerCompany', 'warehouse', 'rack'])
            ->whereHas('warehouse', fn ($query) => $query->where('company_id', $activeCompany->id))
            ->where('owner_company_id', '!=', $activeCompany->id)
            ->positive()
            ->get();

        $statusSummary = collect(StockStatus::cases())->mapWithKeys(function (StockStatus $status) use ($ownedBalances): array {
            $sum = $ownedBalances
                ->where('status', $status)
                ->reduce(fn (string $carry, StockBalance $item): string => bcadd($carry, (string) $item->quantity, 3), '0.000');

            return [$status->value => $sum];
        });

        $managedWarehousesCount = Warehouse::query()
            ->managedBy($activeCompany)
            ->where('is_active', true)
            ->count();

        $accessibleProductsCount = $companyContext->isGroupOversight()
            ? Product::query()->where('is_active', true)->count()
            : Product::query()->accessibleByCompany($activeCompany)->where('is_active', true)->count();

        $visibleLotsCount = $companyContext->isGroupOversight()
            ? Lot::query()->count()
            : Lot::query()->visibleToCompany($activeCompany)->count();

        $recentMovements = StockMovement::query()
            ->with(['product', 'lot', 'fromOwnerCompany', 'toOwnerCompany', 'fromWarehouse', 'toWarehouse', 'fromRack', 'toRack', 'performer'])
            ->visibleToCompany($activeCompany)
            ->latest('occurred_at')
            ->latest('id')
            ->limit(8)
            ->get();

        return view('dashboard.index', [
            'ownedBalances' => $ownedBalances,
            'custodialBalances' => $custodialBalances,
            'statusSummary' => $statusSummary,
            'managedWarehousesCount' => $managedWarehousesCount,
            'accessibleProductsCount' => $accessibleProductsCount,
            'visibleLotsCount' => $visibleLotsCount,
            'recentMovements' => $recentMovements,
        ]);
    }
}
