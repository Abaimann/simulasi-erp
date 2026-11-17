<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Lot;
use App\Models\Product;
use App\Models\Warehouse;
use App\Policies\LotPolicy;
use App\Policies\ProductPolicy;
use App\Policies\WarehousePolicy;
use App\Services\ActiveCompanyContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(ActiveCompanyContext::class);
    }

    public function boot(): void
    {
        Carbon::setLocale('id');

        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Warehouse::class, WarehousePolicy::class);
        Gate::policy(Lot::class, LotPolicy::class);

        Gate::define('manage-group-master', function (): bool {
            return app(ActiveCompanyContext::class)->canManageGroupMaster();
        });

        Gate::define('view-group-summary', function (): bool {
            return app(ActiveCompanyContext::class)->canViewGroupSummary();
        });

        Gate::define('view-customer-pricing', function (): bool {
            return app(ActiveCompanyContext::class)->canViewCustomerPrice();
        });

        Gate::define('record-rack-transfer', function ($user, ?Warehouse $warehouse = null): bool {
            $context = app(ActiveCompanyContext::class);

            if ($warehouse === null) {
                return $context->hasRole(UserRole::PetugasLogistik);
            }

            return $context->canRecordRackTransfer($warehouse);
        });
    }
}
