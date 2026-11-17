<?php

declare(strict_types=1);

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreWarehouseRackRequest;
use App\Models\Warehouse;
use App\Models\WarehouseRack;
use App\Services\ActiveCompanyContext;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class WarehouseRackController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function store(
        StoreWarehouseRackRequest $request,
        Warehouse $warehouse,
        ActiveCompanyContext $companyContext
    ): RedirectResponse {
        $validated = $request->validated();

        $rack = $warehouse->racks()->create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'zone_type' => $validated['zone_type'],
            'is_active' => true,
        ]);

        $this->auditLogService->record(
            action: 'warehouse_rack.created',
            description: sprintf('Menambahkan rak %s (%s) di gudang %s.', $rack->code, $rack->name, $warehouse->name),
            actor: $request->user(),
            company: $companyContext->companyOrFail(),
            documentType: 'WarehouseRack',
            documentId: $rack->id,
            documentNumber: $rack->code,
            ipAddress: $request->ip(),
        );

        return redirect()
            ->route('master.warehouses.show', $warehouse)
            ->with('status', sprintf('Rak %s berhasil ditambahkan ke %s.', $rack->code, $warehouse->name));
    }

    public function toggleActive(
        Request $request,
        Warehouse $warehouse,
        WarehouseRack $rack,
        ActiveCompanyContext $companyContext
    ): RedirectResponse {
        if ($rack->warehouse_id !== $warehouse->id) {
            abort(404);
        }

        if (! $companyContext->canManageWarehouseRacks($warehouse)) {
            abort(403, 'Anda tidak berwenang mengubah rak pada gudang ini.');
        }

        $rack->update(['is_active' => ! $rack->is_active]);

        $this->auditLogService->record(
            action: 'warehouse_rack.toggled',
            description: sprintf(
                'Mengubah status rak %s menjadi %s.',
                $rack->code,
                $rack->is_active ? 'Aktif' : 'Nonaktif'
            ),
            actor: $request->user(),
            company: $companyContext->companyOrFail(),
            documentType: 'WarehouseRack',
            documentId: $rack->id,
            documentNumber: $rack->code,
            ipAddress: $request->ip(),
        );

        return redirect()
            ->route('master.warehouses.show', $warehouse)
            ->with('status', sprintf('Status rak %s diperbarui.', $rack->code));
    }
}
