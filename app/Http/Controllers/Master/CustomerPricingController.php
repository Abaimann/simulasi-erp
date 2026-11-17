<?php

declare(strict_types=1);

namespace App\Http\Controllers\Master;

use App\Enums\ProductCategory;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class CustomerPricingController extends Controller
{
    public function index(): View
    {
        Gate::authorize('view-customer-pricing');

        $products = Product::query()
            ->where('category', ProductCategory::FinishedGood)
            ->where('is_active', true)
            ->orderBy('sku')
            ->get();

        return view('master.pricing.index', [
            'products' => $products,
        ]);
    }
}
