<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Services\ActiveCompanyContext;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

final class EnsureActiveCompany
{
    public const string SESSION_KEY = 'active_company_id';

    public function __construct(
        private readonly ActiveCompanyContext $companyContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda sedang dinonaktifkan. Hubungi Admin Grup.',
            ]);
        }

        $accessibleCompanies = $user->accessibleCompanies();

        if ($accessibleCompanies->isEmpty()) {
            abort(403, 'Akun Anda belum memiliki keanggotaan PT yang aktif.');
        }

        $requestedCompanyId = $request->session()->get(self::SESSION_KEY);
        $activeCompany = $this->resolveActiveCompany($user->id, $accessibleCompanies, $requestedCompanyId);

        if ($activeCompany === null) {
            abort(403, 'Akses PT aktif tidak valid.');
        }

        $request->session()->put(self::SESSION_KEY, $activeCompany->id);
        $this->companyContext->set($user, $activeCompany);

        View::share('activeCompany', $activeCompany);
        View::share('accessibleCompanies', $accessibleCompanies);
        View::share('activeCompanyRoles', $this->companyContext->roles());
        View::share('companyContext', $this->companyContext);

        return $next($request);
    }

    /**
     * @param  Collection<int, Company>  $accessibleCompanies
     */
    private function resolveActiveCompany(int $userId, $accessibleCompanies, mixed $requestedCompanyId): ?Company
    {
        if (is_numeric($requestedCompanyId)) {
            $matched = $accessibleCompanies->firstWhere('id', (int) $requestedCompanyId);

            if ($matched !== null) {
                return $matched;
            }
        }

        $defaultMembership = CompanyMembership::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();

        if ($defaultMembership !== null) {
            $defaultCompany = $accessibleCompanies->firstWhere('id', $defaultMembership->company_id);

            if ($defaultCompany !== null) {
                return $defaultCompany;
            }
        }

        return $accessibleCompanies->first();
    }
}
