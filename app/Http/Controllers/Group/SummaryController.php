<?php

declare(strict_types=1);

namespace App\Http\Controllers\Group;

use App\Enums\StockStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class SummaryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('view-group-summary');

        $companies = Company::query()
            ->withCount([
                'memberships as active_members_count' => fn ($q) => $q->where('is_active', true),
                'warehouses',
            ])
            ->orderBy('id')
            ->get();

        $allPositiveBalances = StockBalance::query()
            ->with(['product', 'lot', 'ownerCompany', 'warehouse.company', 'rack'])
            ->positive()
            ->get();

        $companySummaries = $companies->map(function (Company $company) use ($allPositiveBalances): array {
            $ownedTotal = $allPositiveBalances
                ->where('owner_company_id', $company->id)
                ->reduce(fn (string $carry, StockBalance $row): string => bcadd($carry, (string) $row->quantity, 3), '0.000');

            $storedInManagedWarehouses = $allPositiveBalances
                ->filter(fn (StockBalance $row): bool => $row->warehouse?->company_id === $company->id)
                ->reduce(fn (string $carry, StockBalance $row): string => bcadd($carry, (string) $row->quantity, 3), '0.000');

            return [
                'company' => $company,
                'owned_total' => $ownedTotal,
                'stored_total' => $storedInManagedWarehouses,
            ];
        });

        $products = Product::query()->where('is_active', true)->orderBy('sku')->get();

        $recentMovements = StockMovement::query()
            ->with(['product', 'lot', 'fromOwnerCompany', 'toOwnerCompany', 'fromWarehouse', 'toWarehouse', 'performer'])
            ->latest('occurred_at')
            ->latest('id')
            ->limit(10)
            ->get();

        $recentAuditLogs = AuditLog::query()
            ->with(['actor', 'company'])
            ->latest('occurred_at')
            ->latest('id')
            ->limit(10)
            ->get();

        return view('group.summary', [
            'companySummaries' => $companySummaries,
            'allPositiveBalances' => $allPositiveBalances,
            'products' => $products,
            'statusCases' => StockStatus::cases(),
            'recentMovements' => $recentMovements,
            'recentAuditLogs' => $recentAuditLogs,
        ]);
    }
}
