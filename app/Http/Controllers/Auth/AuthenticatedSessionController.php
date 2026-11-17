<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureActiveCompany;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Company;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Email atau kata sandi tidak sesuai.']);
        }

        $user = Auth::user();

        if ($user === null || ! $user->is_active) {
            Auth::logout();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Akun Anda sedang dinonaktifkan. Hubungi Admin Grup.']);
        }

        $accessibleCompanies = $user->accessibleCompanies();

        if ($accessibleCompanies->isEmpty()) {
            Auth::logout();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Akun belum memiliki keanggotaan PT yang aktif. Hubungi Admin Grup.']);
        }

        $request->session()->regenerate();

        $defaultMembership = $user->activeMemberships()
            ->where('is_default', true)
            ->first();

        /** @var Company $initialCompany */
        $initialCompany = $defaultMembership !== null
            ? ($accessibleCompanies->firstWhere('id', $defaultMembership->company_id) ?? $accessibleCompanies->first())
            : $accessibleCompanies->first();

        $request->session()->put(EnsureActiveCompany::SESSION_KEY, $initialCompany->id);

        $this->auditLogService->record(
            action: 'auth.login',
            description: sprintf('Pengguna %s masuk ke Simulasi ERP pada konteks %s.', $user->name, $initialCompany->short_name),
            actor: $user,
            company: $initialCompany,
            documentType: 'User',
            documentId: $user->id,
            documentNumber: $user->email,
            ipAddress: $request->ip(),
        );

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $activeCompanyId = $request->session()->get(EnsureActiveCompany::SESSION_KEY);
        $activeCompany = is_numeric($activeCompanyId) ? Company::query()->find((int) $activeCompanyId) : null;

        if ($user !== null) {
            $this->auditLogService->record(
                action: 'auth.logout',
                description: sprintf('Pengguna %s keluar dari sesi Simulasi ERP.', $user->name),
                actor: $user,
                company: $activeCompany,
                documentType: 'User',
                documentId: $user->id,
                documentNumber: $user->email,
                ipAddress: $request->ip(),
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
