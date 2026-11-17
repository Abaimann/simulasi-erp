<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Enums\StockStatus;
use App\Enums\UserRole;
use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreRackTransferRequest;
use App\Models\Company;
use App\Models\Lot;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseRack;
use App\Services\ActiveCompanyContext;
use App\Services\StockLedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

final class StockMovementController extends Controller
{
    public function index(Request $request, ActiveCompanyContext $companyContext): View
    {
        $activeCompany = $companyContext->companyOrFail();

        $query = StockMovement::query()
            ->with([
                'product',
                'lot',
                'fromOwnerCompany',
                'toOwnerCompany',
                'fromWarehouse',
                'toWarehouse',
                'fromRack',
                'toRack',
                'performer',
                'performedCompany',
            ])
            ->latest('occurred_at')
            ->latest('id');

        if (! $companyContext->isGroupOversight()) {
            $query->visibleToCompany($activeCompany);
        }

        $canTransferRacks = $companyContext->hasRole(UserRole::PetugasLogistik);
        $transferableBalances = collect();
        $managedRacks = collect();

        if ($canTransferRacks) {
            $transferableBalances = StockBalance::query()
                ->with(['product', 'lot', 'ownerCompany', 'warehouse', 'rack'])
                ->whereHas('warehouse', fn ($q) => $q->where('company_id', $activeCompany->id))
                ->whereIn('status', [StockStatus::Available->value, StockStatus::Quarantine->value])
                ->positive()
                ->get();

            $managedRacks = WarehouseRack::query()
                ->with('warehouse')
                ->where('is_active', true)
                ->whereHas('warehouse', fn ($q) => $q->where('company_id', $activeCompany->id)->where('is_active', true))
                ->orderBy('code')
                ->get();
        }

        return view('inventory.movements.index', [
            'movements' => $query->paginate(20),
            'canTransferRacks' => $canTransferRacks,
            'transferableBalances' => $transferableBalances,
            'managedRacks' => $managedRacks,
        ]);
    }

    public function storeRackTransfer(
        StoreRackTransferRequest $request,
        ActiveCompanyContext $companyContext,
        StockLedgerService $ledgerService
    ): RedirectResponse {
        $activeCompany = $companyContext->companyOrFail();
        $validated = $request->validated();

        $warehouse = Warehouse::query()->findOrFail((int) $validated['warehouse_id']);

        if ($warehouse->company_id !== $activeCompany->id) {
            abort(403, 'Gudang tersebut bukan dikelola oleh PT aktif Anda.');
        }

        $product = Product::query()->findOrFail((int) $validated['product_id']);
        $lot = Lot::query()->findOrFail((int) $validated['lot_id']);
        $ownerCompany = Company::query()->findOrFail((int) $validated['owner_company_id']);
        $fromRack = WarehouseRack::query()->findOrFail((int) $validated['from_rack_id']);
        $toRack = WarehouseRack::query()->findOrFail((int) $validated['to_rack_id']);
        $status = StockStatus::from((string) $validated['status']);

        try {
            $movement = $ledgerService->transferBetweenRacks(
                product: $product,
                lot: $lot,
                ownerCompany: $ownerCompany,
                warehouse: $warehouse,
                fromRack: $fromRack,
                toRack: $toRack,
                status: $status,
                quantity: $validated['quantity'],
                actor: $request->user(),
                performedCompany: $activeCompany,
                notes: $validated['notes'] ?? null,
                idempotencyKey: $validated['idempotency_key'] ?? null,
            );
        } catch (InsufficientStockException|InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['quantity' => $exception->getMessage()]);
        }

        return redirect()
            ->route('inventory.movements.index')
            ->with('status', sprintf(
                'Pergerakan stok %s tercatat. Kepemilikan tetap pada %s.',
                $movement->movement_number,
                $ownerCompany->name
            ));
    }
}
