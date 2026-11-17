<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CompanyCode;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use App\Models\Warehouse;
use RuntimeException;

final class ActiveCompanyContext
{
    private ?User $user = null;

    private ?Company $company = null;

    /** @var list<UserRole> */
    private array $roles = [];

    public function set(User $user, Company $company): void
    {
        $this->user = $user;
        $this->company = $company;
        $this->roles = $user->rolesInCompany($company);
    }

    public function user(): ?User
    {
        return $this->user;
    }

    public function company(): ?Company
    {
        return $this->company;
    }

    public function companyOrFail(): Company
    {
        if ($this->company === null) {
            throw new RuntimeException('Konteks PT aktif belum diinisialisasi.');
        }

        return $this->company;
    }

    /**
     * @return list<UserRole>
     */
    public function roles(): array
    {
        return $this->roles;
    }

    /**
     * @param  UserRole|list<UserRole>  $required
     */
    public function hasRole(UserRole|array $required): bool
    {
        $requiredRoles = is_array($required) ? $required : [$required];

        foreach ($this->roles as $role) {
            if (in_array($role, $requiredRoles, true)) {
                return true;
            }
        }

        return false;
    }

    public function canViewCustomerPrice(): bool
    {
        if ($this->company === null) {
            return false;
        }

        foreach ($this->roles as $role) {
            if ($role->canViewCustomerPrice($this->company->code)) {
                return true;
            }
        }

        return false;
    }

    public function canManageGroupMaster(): bool
    {
        return $this->hasRole(UserRole::AdminGrup);
    }

    public function canViewGroupSummary(): bool
    {
        return $this->hasRole(UserRole::ManajemenGrup);
    }

    public function isGroupOversight(): bool
    {
        return $this->hasRole([UserRole::AdminGrup, UserRole::ManajemenGrup]);
    }

    public function canManageWarehouseRacks(Warehouse $warehouse): bool
    {
        if ($this->company === null || $warehouse->company_id !== $this->company->id) {
            return false;
        }

        return $this->hasRole([UserRole::PetugasLogistik, UserRole::AdminGrup]);
    }

    public function canRecordRackTransfer(Warehouse $warehouse): bool
    {
        if ($this->company === null || $warehouse->company_id !== $this->company->id) {
            return false;
        }

        return $this->hasRole(UserRole::PetugasLogistik);
    }

    public function activeCompanyCode(): ?CompanyCode
    {
        return $this->company?->code;
    }
}
