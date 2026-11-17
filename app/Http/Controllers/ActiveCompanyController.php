<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureActiveCompany;
use App\Http\Requests\SwitchCompanyRequest;
use App\Models\Company;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;

final class ActiveCompanyController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function switch(SwitchCompanyRequest $request): RedirectResponse
    {
        $user = $request->user();
        $targetCompanyId = (int) $request->validated('company_id');

        if ($user === null || ! $user->hasActiveMembershipIn($targetCompanyId)) {
            abort(403, 'Anda tidak memiliki keanggotaan aktif pada PT yang dipilih.');
        }

        $targetCompany = Company::query()
            ->where('is_active', true)
            ->findOrFail($targetCompanyId);

        $request->session()->put(EnsureActiveCompany::SESSION_KEY, $targetCompany->id);

        $this->auditLogService->record(
            action: 'company.switched',
            description: sprintf('Pengguna %s mengganti PT aktif ke %s.', $user->name, $targetCompany->name),
            actor: $user,
            company: $targetCompany,
            documentType: 'Company',
            documentId: $targetCompany->id,
            documentNumber: $targetCompany->code->value,
            ipAddress: $request->ip(),
        );

        return redirect()
            ->route('dashboard')
            ->with('status', sprintf('Konteks PT aktif dipindahkan ke %s.', $targetCompany->name));
    }
}
