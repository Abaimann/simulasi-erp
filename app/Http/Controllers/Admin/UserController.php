<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\User;
use App\Services\ActiveCompanyContext;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

final class UserController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function index(): View
    {
        Gate::authorize('manage-group-master');

        $users = User::query()
            ->with(['memberships.company'])
            ->orderBy('name')
            ->get();

        $companies = Company::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        return view('admin.users.index', [
            'users' => $users,
            'companies' => $companies,
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(StoreUserRequest $request, ActiveCompanyContext $companyContext): RedirectResponse
    {
        $validated = $request->validated();
        $activeCompany = $companyContext->companyOrFail();

        $user = DB::transaction(function () use ($validated, $request, $activeCompany): User {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => strtolower($validated['email']),
                'password' => Hash::make($validated['password']),
                'is_active' => true,
            ]);

            CompanyMembership::query()->create([
                'user_id' => $user->id,
                'company_id' => (int) $validated['company_id'],
                'role' => $validated['role'],
                'is_active' => true,
                'is_default' => true,
                'assigned_by' => $request->user()?->id,
            ]);

            $this->auditLogService->record(
                action: 'user.created',
                description: sprintf('Membuat akun pengguna %s (%s) dengan role %s.', $user->name, $user->email, $validated['role']),
                actor: $request->user(),
                company: $activeCompany,
                documentType: 'User',
                documentId: $user->id,
                documentNumber: $user->email,
                ipAddress: $request->ip(),
            );

            return $user;
        });

        return redirect()
            ->route('admin.users.index')
            ->with('status', sprintf('Pengguna %s berhasil ditambahkan.', $user->name));
    }
}
