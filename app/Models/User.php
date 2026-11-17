<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(CompanyMembership::class);
    }

    public function activeMemberships(): HasMany
    {
        return $this->memberships()
            ->where('is_active', true)
            ->whereHas('company', fn ($query) => $query->where('is_active', true));
    }

    /**
     * @return Collection<int, Company>
     */
    public function accessibleCompanies(): Collection
    {
        return Company::query()
            ->where('is_active', true)
            ->whereHas('memberships', function ($query): void {
                $query->where('user_id', $this->id)
                    ->where('is_active', true);
            })
            ->orderBy('id')
            ->get();
    }

    /**
     * @return list<UserRole>
     */
    public function rolesInCompany(Company $company): array
    {
        return $this->activeMemberships()
            ->where('company_id', $company->id)
            ->get()
            ->map(fn (CompanyMembership $membership): UserRole => $membership->role)
            ->values()
            ->all();
    }

    /**
     * @param  UserRole|list<UserRole>  $roles
     */
    public function hasRoleInCompany(Company $company, UserRole|array $roles): bool
    {
        $requiredRoles = is_array($roles) ? $roles : [$roles];
        $assignedRoles = $this->rolesInCompany($company);

        foreach ($assignedRoles as $assignedRole) {
            if (in_array($assignedRole, $requiredRoles, true)) {
                return true;
            }
        }

        return false;
    }

    public function hasActiveMembershipIn(int $companyId): bool
    {
        return $this->activeMemberships()
            ->where('company_id', $companyId)
            ->exists();
    }
}
