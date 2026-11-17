<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Services\ActiveCompanyContext;

final class ProductPolicy
{
    public function __construct(
        private readonly ActiveCompanyContext $companyContext
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->companyContext->company() !== null;
    }

    public function view(User $user, Product $product): bool
    {
        $activeCompany = $this->companyContext->company();

        if ($activeCompany === null) {
            return false;
        }

        if ($this->companyContext->isGroupOversight()) {
            return true;
        }

        return $product->isAccessibleByCompany($activeCompany);
    }

    public function create(User $user): bool
    {
        return $this->companyContext->canManageGroupMaster();
    }

    public function update(User $user, Product $product): bool
    {
        return $this->companyContext->canManageGroupMaster();
    }
}
