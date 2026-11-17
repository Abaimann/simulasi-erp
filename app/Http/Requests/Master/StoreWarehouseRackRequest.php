<?php

declare(strict_types=1);

namespace App\Http\Requests\Master;

use App\Enums\RackZoneType;
use App\Models\Warehouse;
use App\Services\ActiveCompanyContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreWarehouseRackRequest extends FormRequest
{
    public function authorize(): bool
    {
        $warehouse = $this->route('warehouse');

        if (! $warehouse instanceof Warehouse) {
            return false;
        }

        return app(ActiveCompanyContext::class)->canManageWarehouseRacks($warehouse);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        /** @var Warehouse $warehouse */
        $warehouse = $this->route('warehouse');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('warehouse_racks', 'code')->where('warehouse_id', $warehouse->id),
            ],
            'name' => ['required', 'string', 'max:120'],
            'zone_type' => ['required', Rule::enum(RackZoneType::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode rak wajib diisi.',
            'code.unique' => 'Kode rak sudah digunakan pada gudang ini.',
            'name.required' => 'Nama rak wajib diisi.',
            'zone_type.required' => 'Zona rak wajib dipilih.',
        ];
    }
}
