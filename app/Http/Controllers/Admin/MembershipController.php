<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMembershipRequest;
use App\Models\Company;
use App\Models\CompanyMembership;
use App\Services\ActiveCompanyContext;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class MembershipController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function store(StoreMembershipRequest $request, ActiveCompanyContext $companyContext): RedirectResponse
    {
        $validated = $request->validated();
        $activeCompany = $companyContext->companyOrFail();
        $targetCompany = Company::query()->findOrFail((int) $validated['company_id']);
        $role = UserRole::from((string) $validated['role']);

        DB::transaction(function () use ($validated, $request, $activeCompany, $targetCompany, $role): void {
            $isDefault = (bool) ($validated['is_default'] ?? false);

            if ($isDefault) {
                CompanyMembership::query()
                    ->where('user_id', (int) $validated['user_id'])
                    ->update(['is_default' => false]);
            }

            $membership = CompanyMembership::query()->create([
                'user_id' => (int) $validated['user_id'],
                'company_id' => $targetCompany->id,
                'role' => $role,
                'is_active' => true,
                'is_default' => $isDefault,
                'assigned_by' => $request->user()?->id,
            ]);

            $membership->load('user');

            $this->auditLogService->record(
                action: 'membership.assigned',
                description: sprintf(
                    'Menetapkan role %s untuk %s di %s.',
                    $role->label(),
                    $membership->user->name,
                    $targetCompany->short_name
                ),
                actor: $request->user(),
                company: $activeCompany,
                documentType: 'CompanyMembership',
                documentId: $membership->id,
                documentNumber: $targetCompany->code->value,
                ipAddress: $request->ip(),
            );
        });

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Keanggotaan dan role berhasil ditambahkan.');
    }

    public function toggleActive(
        Request $request,
        CompanyMembership $membership,
        ActiveCompanyContext $companyContext
    ): RedirectResponse {
        Gate::authorize('manage-group-master');

        $currentUser = $request->user();

        if ($currentUser !== null
            && $membership->user_id === $currentUser->id
            && $membership->role === UserRole::AdminGrup
            && $membership->is_active) {
            $otherActiveAdminCount = CompanyMembership::query()
                ->where('user_id', $currentUser->id)
                ->where('role', UserRole::AdminGrup->value)
                ->where('is_active', true)
                ->where('id', '!=', $membership->id)
                ->count();

            if ($otherActiveAdminCount === 0) {
                return redirect()
                    ->route('admin.users.index')
                    ->withErrors(['membership' => 'Anda tidak dapat menonaktifkan satu-satunya keanggotaan Admin Grup milik Anda sendiri.']);
            }
        }

        $membership->update(['is_active' => ! $membership->is_active]);
        $membership->load(['user', 'company']);

        $this->auditLogService->record(
            action: 'membership.toggled',
            description: sprintf(
                'Mengubah status keanggotaan %s (%s di %s) menjadi %s.',
                $membership->user->name,
                $membership->role->label(),
                $membership->company->short_name,
                $membership->is_active ? 'Aktif' : 'Nonaktif'
            ),
            actor: $currentUser,
            company: $companyContext->companyOrFail(),
            documentType: 'CompanyMembership',
            documentId: $membership->id,
            documentNumber: $membership->company->code->value,
            ipAddress: $request->ip(),
        );

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Status keanggotaan pengguna diperbarui.');
    }
}
