<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\CompanyBusinessType;
use App\Enums\CompanyCode;
use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

final class UserRoleTest extends TestCase
{
    public function test_role_compatibility_matches_company_business_type(): void
    {
        $this->assertTrue(UserRole::SalesDistribusi->isAllowedForCompanyType(CompanyBusinessType::Distribution));
        $this->assertFalse(UserRole::SalesDistribusi->isAllowedForCompanyType(CompanyBusinessType::Manufacturing));
        $this->assertFalse(UserRole::SalesDistribusi->isAllowedForCompanyType(CompanyBusinessType::Logistics));

        $this->assertTrue(UserRole::SupervisorProduksi->isAllowedForCompanyType(CompanyBusinessType::Manufacturing));
        $this->assertFalse(UserRole::SupervisorProduksi->isAllowedForCompanyType(CompanyBusinessType::Distribution));

        $this->assertTrue(UserRole::PetugasQc->isAllowedForCompanyType(CompanyBusinessType::Manufacturing));
        $this->assertFalse(UserRole::PetugasQc->isAllowedForCompanyType(CompanyBusinessType::Logistics));

        $this->assertTrue(UserRole::PetugasLogistik->isAllowedForCompanyType(CompanyBusinessType::Logistics));
        $this->assertFalse(UserRole::PetugasLogistik->isAllowedForCompanyType(CompanyBusinessType::Distribution));

        $this->assertTrue(UserRole::Purchasing->isAllowedForCompanyType(CompanyBusinessType::Manufacturing));
        $this->assertTrue(UserRole::Purchasing->isAllowedForCompanyType(CompanyBusinessType::Distribution));
        $this->assertTrue(UserRole::Finance->isAllowedForCompanyType(CompanyBusinessType::Logistics));
    }

    public function test_customer_selling_price_visibility_rules(): void
    {
        $this->assertTrue(UserRole::SalesDistribusi->canViewCustomerPrice(CompanyCode::Distribusi));
        $this->assertTrue(UserRole::Finance->canViewCustomerPrice(CompanyCode::Distribusi));
        $this->assertTrue(UserRole::ManajemenGrup->canViewCustomerPrice(CompanyCode::Logistik));

        $this->assertFalse(UserRole::PetugasLogistik->canViewCustomerPrice(CompanyCode::Logistik));
        $this->assertFalse(UserRole::PetugasLogistik->canViewCustomerPrice(CompanyCode::Distribusi));
        $this->assertFalse(UserRole::Finance->canViewCustomerPrice(CompanyCode::Logistik));
        $this->assertFalse(UserRole::Finance->canViewCustomerPrice(CompanyCode::Produksi));
        $this->assertFalse(UserRole::SupervisorProduksi->canViewCustomerPrice(CompanyCode::Produksi));
        $this->assertFalse(UserRole::PetugasQc->canViewCustomerPrice(CompanyCode::Produksi));
        $this->assertFalse(UserRole::Purchasing->canViewCustomerPrice(CompanyCode::Distribusi));
        $this->assertFalse(UserRole::AdminGrup->canViewCustomerPrice(CompanyCode::Distribusi));
    }
}
