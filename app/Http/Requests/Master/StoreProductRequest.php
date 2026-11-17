<?php

declare(strict_types=1);

namespace App\Http\Requests\Master;

use App\Enums\ProductCategory;
use App\Services\ActiveCompanyContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(ActiveCompanyContext::class)->canManageGroupMaster();
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', Rule::enum(ProductCategory::class)],
            'unit' => ['required', 'string', 'max:32'],
            'description' => ['nullable', 'string', 'max:500'],
            'intercompany_price' => ['nullable', 'integer', 'min:0'],
            'customer_selling_price' => ['nullable', 'integer', 'min:0'],
            'company_ids' => ['required', 'array', 'min:1'],
            'company_ids.*' => ['integer', 'exists:companies,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sku.required' => 'Kode SKU wajib diisi.',
            'sku.unique' => 'Kode SKU sudah terdaftar.',
            'name.required' => 'Nama produk wajib diisi.',
            'category.required' => 'Kategori produk wajib dipilih.',
            'unit.required' => 'Satuan produk wajib diisi.',
            'company_ids.required' => 'Minimal satu PT harus diberi cakupan akses produk ini.',
        ];
    }
}
