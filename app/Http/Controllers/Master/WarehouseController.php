<?php

declare(strict_types=1);

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\StockBalance;
use App\Models\Warehouse;
use App\Services\ActiveCompanyContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class WarehouseController extends Controller
{
    public function index(ActiveCompanyContext $companyContext): View
    {
        Gate::authorize('viewAny', Warehouse::class);

        $activeCompany = $companyContext->companyOrFail();

        $query = Warehouse::query()
            ->with('company')
            ->withCount('racks')
            ->orderBy('code');

        if (! $companyContext->isGroupOversight()) {
            $query->managedBy($activeCompany);
        }

        return view('master.warehouses.index', [
            'warehouses' => $query->get(),
        ]);
    }

    public function show(Warehouse $warehouse, ActiveCompanyContext $companyContext): View
    {
        Gate::authorize('view', $warehouse);

        $warehouse->load(['company', 'racks' => fn ($query) => $query->orderBy('code')]);

        $balances = StockBalance::query()
            ->with(['product', 'lot', 'ownerCompany', 'rack'])
            ->where('warehouse_id', $warehouse->id)
            ->positive()
            ->orderBy('product_id')
            ->get();

        return view('master.warehouses.show', [
            'warehouse' => $warehouse,
            'balances' => $balances,
            'canManageRacks' => $companyContext->canManageWarehouseRacks($warehouse),
        ]);
    }
}
