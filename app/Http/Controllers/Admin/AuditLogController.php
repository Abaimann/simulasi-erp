<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\ActiveCompanyContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class AuditLogController extends Controller
{
    public function index(ActiveCompanyContext $companyContext): View
    {
        if (! $companyContext->isGroupOversight()) {
            Gate::authorize('manage-group-master');
        }

        $logs = AuditLog::query()
            ->with(['actor', 'company'])
            ->latest('occurred_at')
            ->latest('id')
            ->paginate(25);

        return view('admin.audit-logs.index', [
            'logs' => $logs,
        ]);
    }
}
