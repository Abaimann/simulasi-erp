<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CompanyCode;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $companies = Company::query()->get()->keyBy(fn (Company $c): string => $c->code->value);

        /** @var array<int, array{name: string, email: string, memberships: list<array{company: CompanyCode, role: UserRole, default: bool}>}> $accounts */
        $accounts = [
            [
                'name' => 'Rama Pratama',
                'email' => 'admin@simulasi-erp.local',
                'memberships' => [
                    ['company' => CompanyCode::Distribusi, 'role' => UserRole::AdminGrup, 'default' => true],
                    ['company' => CompanyCode::Produksi, 'role' => UserRole::AdminGrup, 'default' => false],
                    ['company' => CompanyCode::Logistik, 'role' => UserRole::AdminGrup, 'default' => false],
                ],
            ],
            [
                'name' => 'Dewi Kartika',
                'email' => 'manajemen@simulasi-erp.local',
                'memberships' => [
                    ['company' => CompanyCode::Distribusi, 'role' => UserRole::ManajemenGrup, 'default' => true],
                    ['company' => CompanyCode::Produksi, 'role' => UserRole::ManajemenGrup, 'default' => false],
                    ['company' => CompanyCode::Logistik, 'role' => UserRole::ManajemenGrup, 'default' => false],
                ],
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'sales.distribusi@simulasi-erp.local',
                'memberships' => [
                    ['company' => CompanyCode::Distribusi, 'role' => UserRole::SalesDistribusi, 'default' => true],
                ],
            ],
            [
                'name' => 'Siti Rahma',
                'email' => 'purchasing.distribusi@simulasi-erp.local',
                'memberships' => [
                    ['company' => CompanyCode::Distribusi, 'role' => UserRole::Purchasing, 'default' => true],
                ],
            ],
            [
                'name' => 'Andi Wijaya',
                'email' => 'purchasing.produksi@simulasi-erp.local',
                'memberships' => [
                    ['company' => CompanyCode::Produksi, 'role' => UserRole::Purchasing, 'default' => true],
                ],
            ],
            [
                'name' => 'Hendra Kusuma',
                'email' => 'supervisor.produksi@simulasi-erp.local',
                'memberships' => [
                    ['company' => CompanyCode::Produksi, 'role' => UserRole::SupervisorProduksi, 'default' => true],
                ],
            ],
            [
                'name' => 'Nadia Putri',
                'email' => 'qc.produksi@simulasi-erp.local',
                'memberships' => [
                    ['company' => CompanyCode::Produksi, 'role' => UserRole::PetugasQc, 'default' => true],
                ],
            ],
            [
                'name' => 'Teguh Saputra',
                'email' => 'logistik@simulasi-erp.local',
                'memberships' => [
                    ['company' => CompanyCode::Logistik, 'role' => UserRole::PetugasLogistik, 'default' => true],
                ],
            ],
            [
                'name' => 'Rina Melati',
                'email' => 'finance.distribusi@simulasi-erp.local',
                'memberships' => [
                    ['company' => CompanyCode::Distribusi, 'role' => UserRole::Finance, 'default' => true],
                ],
            ],
            [
                'name' => 'Fajar Nugroho',
                'email' => 'finance.produksi@simulasi-erp.local',
                'memberships' => [
                    ['company' => CompanyCode::Produksi, 'role' => UserRole::Finance, 'default' => true],
                ],
            ],
            [
                'name' => 'Lestari Wulandari',
                'email' => 'finance.logistik@simulasi-erp.local',
                'memberships' => [
                    ['company' => CompanyCode::Logistik, 'role' => UserRole::Finance, 'default' => true],
                ],
            ],
            [
                'name' => 'Dian Permata',
                'email' => 'multiperan@simulasi-erp.local',
                'memberships' => [
                    ['company' => CompanyCode::Produksi, 'role' => UserRole::Purchasing, 'default' => true],
                    ['company' => CompanyCode::Logistik, 'role' => UserRole::Finance, 'default' => false],
                ],
            ],
        ];

        $demoPasswordHash = Hash::make('password');

        foreach ($accounts as $account) {
            $user = User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => $demoPasswordHash,
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );

            foreach ($account['memberships'] as $membershipData) {
                $company = $companies->get($membershipData['company']->value);

                if ($company === null) {
                    continue;
                }

                CompanyMembership::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'company_id' => $company->id,
                        'role' => $membershipData['role']->value,
                    ],
                    [
                        'is_active' => true,
                        'is_default' => $membershipData['default'],
                    ]
                );
            }
        }
    }
}
