<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;
use App\Services\ActiveCompanyContext;

final class WarehousePolicy
{
    public function __construct(
        private readonly ActiveCompanyContext $companyContext
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->companyContext->company() !== null;
    }

    public function view(User $user, Warehouse $warehouse): bool
    {
        $activeCompany = $this->companyContext->company();

        if ($activeCompany === null) {
            return false;
        }

        if ($this->companyContext->isGroupOversight()) {
            return true;
        }

        return $warehouse->company_id === $activeCompany->id;
    }

    public function manageRacks(User $user, Warehouse $warehouse): bool
    {
        return $this->companyContext->canManageWarehouseRacks($warehouse);
    }
}
