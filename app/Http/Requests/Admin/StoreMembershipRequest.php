<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\CompanyMembership;
use App\Services\ActiveCompanyContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StoreMembershipRequest extends FormRequest
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_default' => ['nullable', 'boolean'],
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
                    sprintf('Role %s tidak dapat ditugaskan pada %s.', $role->label(), $company->short_name)
                );
            }

            $exists = CompanyMembership::query()
                ->where('user_id', (int) $this->input('user_id'))
                ->where('company_id', (int) $this->input('company_id'))
                ->where('role', (string) $this->input('role'))
                ->exists();

            if ($exists) {
                $validator->errors()->add('role', 'Keanggotaan dan role tersebut sudah terdaftar untuk pengguna ini.');
            }
        });
    }
}
