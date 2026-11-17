<?php

declare(strict_types=1);

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreProductRequest;
use App\Models\Company;
use App\Models\Product;
use App\Models\StockBalance;
use App\Services\ActiveCompanyContext;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class ProductController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function index(ActiveCompanyContext $companyContext): View
    {
        Gate::authorize('viewAny', Product::class);

        $activeCompany = $companyContext->companyOrFail();

        $query = Product::query()
            ->with('accessibleCompanies')
            ->orderBy('sku');

        if (! $companyContext->isGroupOversight()) {
            $query->accessibleByCompany($activeCompany);
        }

        return view('master.products.index', [
            'products' => $query->get(),
            'canViewCustomerPrice' => $companyContext->canViewCustomerPrice(),
            'canManageProducts' => $companyContext->canManageGroupMaster(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Product::class);

        return view('master.products.create', [
            'companies' => Company::query()->where('is_active', true)->orderBy('id')->get(),
        ]);
    }

    public function store(StoreProductRequest $request, ActiveCompanyContext $companyContext): RedirectResponse
    {
        $validated = $request->validated();
        $activeCompany = $companyContext->companyOrFail();

        $product = DB::transaction(function () use ($validated, $request, $activeCompany): Product {
            $product = Product::query()->create([
                'sku' => strtoupper($validated['sku']),
                'name' => $validated['name'],
                'category' => $validated['category'],
                'unit' => $validated['unit'],
                'description' => $validated['description'] ?? null,
                'intercompany_price' => $validated['intercompany_price'] ?? null,
                'customer_selling_price' => $validated['customer_selling_price'] ?? null,
                'is_active' => true,
            ]);

            $product->accessibleCompanies()->sync($validated['company_ids']);

            $this->auditLogService->record(
                action: 'product.created',
                description: sprintf('Menambahkan produk master %s (%s).', $product->name, $product->sku),
                actor: $request->user(),
                company: $activeCompany,
                documentType: 'Product',
                documentId: $product->id,
                documentNumber: $product->sku,
                ipAddress: $request->ip(),
            );

            return $product;
        });

        return redirect()
            ->route('master.products.show', $product)
            ->with('status', sprintf('Produk %s berhasil ditambahkan.', $product->name));
    }

    public function show(Product $product, ActiveCompanyContext $companyContext): View
    {
        Gate::authorize('view', $product);

        $activeCompany = $companyContext->companyOrFail();
        $product->load('accessibleCompanies');

        $balancesQuery = StockBalance::query()
            ->with(['lot', 'ownerCompany', 'warehouse.company', 'rack'])
            ->where('product_id', $product->id)
            ->positive();

        if (! $companyContext->isGroupOversight()) {
            $balancesQuery->visibleToCompany($activeCompany);
        }

        return view('master.products.show', [
            'product' => $product,
            'balances' => $balancesQuery->get(),
            'canViewCustomerPrice' => $companyContext->canViewCustomerPrice(),
        ]);
    }
}
