<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Lot;
use App\Models\User;
use App\Services\ActiveCompanyContext;

final class LotPolicy
{
    public function __construct(
        private readonly ActiveCompanyContext $companyContext
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->companyContext->company() !== null;
    }

    public function view(User $user, Lot $lot): bool
    {
        $activeCompany = $this->companyContext->company();

        if ($activeCompany === null) {
            return false;
        }

        if ($this->companyContext->isGroupOversight()) {
            return true;
        }

        $lot->loadMissing('product');

        if (! $lot->product->isAccessibleByCompany($activeCompany)) {
            return false;
        }

        return $lot->isVisibleToCompany($activeCompany);
    }
}
