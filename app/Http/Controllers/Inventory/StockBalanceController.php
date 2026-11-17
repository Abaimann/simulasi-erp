<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Enums\StockStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockBalance;
use App\Services\ActiveCompanyContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class StockBalanceController extends Controller
{
    public function index(Request $request, ActiveCompanyContext $companyContext): View
    {
        $activeCompany = $companyContext->companyOrFail();

        $query = StockBalance::query()
            ->with(['product', 'lot', 'ownerCompany', 'warehouse.company', 'rack'])
            ->positive()
            ->orderBy('product_id')
            ->orderBy('owner_company_id');

        if (! $companyContext->isGroupOversight()) {
            $query->visibleToCompany($activeCompany);
        }

        $selectedStatus = $request->string('status')->toString();

        if ($selectedStatus !== '' && StockStatus::tryFrom($selectedStatus) !== null) {
            $query->where('status', $selectedStatus);
        }

        $selectedProductId = $request->integer('product_id');

        if ($selectedProductId > 0) {
            $query->where('product_id', $selectedProductId);
        }

        $ownershipScope = $request->string('kepemilikan')->toString();

        if ($ownershipScope === 'owned') {
            $query->where('owner_company_id', $activeCompany->id);
        } elseif ($ownershipScope === 'custodial') {
            $query->where('owner_company_id', '!=', $activeCompany->id)
                ->whereHas('warehouse', fn ($q) => $q->where('company_id', $activeCompany->id));
        }

        $balances = $query->get();

        $products = $companyContext->isGroupOversight()
            ? Product::query()->orderBy('sku')->get()
            : Product::query()->accessibleByCompany($activeCompany)->orderBy('sku')->get();

        return view('inventory.balances.index', [
            'balances' => $balances,
            'products' => $products,
            'selectedStatus' => $selectedStatus,
            'selectedProductId' => $selectedProductId,
            'ownershipScope' => $ownershipScope,
        ]);
    }
}
