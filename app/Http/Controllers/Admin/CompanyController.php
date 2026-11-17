<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class CompanyController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage-group-master');

        $companies = Company::query()
            ->withCount([
                'memberships as active_memberships_count' => fn ($query) => $query->where('is_active', true),
                'warehouses',
            ])
            ->orderBy('id')
            ->get();

        return view('admin.companies.index', [
            'companies' => $companies,
        ]);
    }
}
