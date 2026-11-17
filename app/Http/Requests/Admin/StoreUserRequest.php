<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\Company;
use App\Services\ActiveCompanyContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StoreUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'role' => ['required', Rule::enum(UserRole::class)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $company = Company::query()->find((int) $this->input('company_id'));
            $role = UserRole::tryFrom((string) $this->input('role'));

            if ($company !== null && $role !== null && ! $role->isAllowedForCompanyType($company->business_type)) {
                $validator->errors()->add(
                    'role',
                    sprintf('Role %s tidak sesuai untuk jenis usaha %s.', $role->label(), $company->short_name)
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ];
    }
}
