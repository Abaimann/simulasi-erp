<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory;

use App\Enums\StockStatus;
use App\Enums\UserRole;
use App\Models\Warehouse;
use App\Services\ActiveCompanyContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreRackTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        $warehouseId = $this->input('warehouse_id');

        if (! is_numeric($warehouseId)) {
            return app(ActiveCompanyContext::class)->hasRole(UserRole::PetugasLogistik);
        }

        $warehouse = Warehouse::query()->find((int) $warehouseId);

        if ($warehouse === null) {
            return app(ActiveCompanyContext::class)->hasRole(UserRole::PetugasLogistik);
        }

        return app(ActiveCompanyContext::class)->canRecordRackTransfer($warehouse);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'lot_id' => ['required', 'integer', 'exists:lots,id'],
            'owner_company_id' => ['required', 'integer', 'exists:companies,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'from_rack_id' => ['required', 'integer', 'exists:warehouse_racks,id'],
            'to_rack_id' => ['required', 'integer', 'different:from_rack_id', 'exists:warehouse_racks,id'],
            'status' => ['required', Rule::in([StockStatus::Available->value, StockStatus::Quarantine->value])],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string', 'max:500'],
            'idempotency_key' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'to_rack_id.different' => 'Rak tujuan harus berbeda dengan rak asal.',
            'quantity.gt' => 'Kuantitas perpindahan harus lebih dari 0.',
        ];
    }
}
