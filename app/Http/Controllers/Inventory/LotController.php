<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Lot;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Services\ActiveCompanyContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class LotController extends Controller
{
    public function index(ActiveCompanyContext $companyContext): View
    {
        Gate::authorize('viewAny', Lot::class);

        $activeCompany = $companyContext->companyOrFail();

        $query = Lot::query()
            ->with(['product', 'originCompany'])
            ->orderByDesc('manufactured_at')
            ->orderByDesc('id');

        if (! $companyContext->isGroupOversight()) {
            $query->whereHas('product', fn ($q) => $q->accessibleByCompany($activeCompany))
                ->visibleToCompany($activeCompany);
        }

        return view('inventory.lots.index', [
            'lots' => $query->get(),
        ]);
    }

    public function show(Lot $lot, ActiveCompanyContext $companyContext): View
    {
        Gate::authorize('view', $lot);

        $activeCompany = $companyContext->companyOrFail();
        $lot->load(['product', 'originCompany']);

        $balancesQuery = StockBalance::query()
            ->with(['ownerCompany', 'warehouse.company', 'rack'])
            ->where('lot_id', $lot->id)
            ->positive();

        $movementsQuery = StockMovement::query()
            ->with(['fromOwnerCompany', 'toOwnerCompany', 'fromWarehouse', 'toWarehouse', 'fromRack', 'toRack', 'performer', 'performedCompany'])
            ->where('lot_id', $lot->id)
            ->latest('occurred_at')
            ->latest('id');

        if (! $companyContext->isGroupOversight()) {
            $balancesQuery->visibleToCompany($activeCompany);
            $movementsQuery->visibleToCompany($activeCompany);
        }

        return view('inventory.lots.show', [
            'lot' => $lot,
            'balances' => $balancesQuery->get(),
            'movements' => $movementsQuery->get(),
        ]);
    }
}
