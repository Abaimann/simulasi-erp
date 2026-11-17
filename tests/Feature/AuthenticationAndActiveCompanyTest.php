<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CompanyCode;
use App\Enums\UserRole;
use App\Http\Middleware\EnsureActiveCompany;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\User;
use Database\Seeders\CompanySeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuthenticationAndActiveCompanyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CompanySeeder::class, DemoUserSeeder::class]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/beranda')->assertRedirect(route('login'));
    }

    public function test_user_can_login_and_sees_active_company_indicator(): void
    {
        $response = $this->post('/login', [
            'email' => 'sales.distribusi@simulasi-erp.local',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $dashboard = $this->get(route('dashboard'));
        $dashboard->assertOk();
        $dashboard->assertSee('PT Reka Distribusi Indonesia');
        $dashboard->assertSee('PT Distribusi');
        $dashboard->assertSee('Sales Distribusi');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.login',
            'document_number' => 'sales.distribusi@simulasi-erp.local',
        ]);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $response = $this->post('/login', [
            'email' => 'sales.distribusi@simulasi-erp.local',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->inactive()->create();
        $distribusi = Company::query()->where('code', CompanyCode::Distribusi->value)->firstOrFail();

        CompanyMembership::query()->create([
            'user_id' => $user->id,
            'company_id' => $distribusi->id,
            'role' => UserRole::SalesDistribusi->value,
            'is_active' => true,
            'is_default' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_multi_company_user_can_switch_only_to_assigned_companies(): void
    {
        $multiUser = User::query()->where('email', 'multiperan@simulasi-erp.local')->firstOrFail();
        $produksi = Company::query()->where('code', CompanyCode::Produksi->value)->firstOrFail();
        $logistik = Company::query()->where('code', CompanyCode::Logistik->value)->firstOrFail();
        $distribusi = Company::query()->where('code', CompanyCode::Distribusi->value)->firstOrFail();

        $this->actingAs($multiUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($produksi->name)
            ->assertSee('Purchasing');

        // Switching to PT Logistik (where multiperan holds Finance role) succeeds
        $switchResponse = $this->actingAs($multiUser)
            ->withSession([EnsureActiveCompany::SESSION_KEY => $produksi->id])
            ->post(route('active-company.switch'), [
                'company_id' => $logistik->id,
            ]);

        $switchResponse->assertRedirect(route('dashboard'));
        $switchResponse->assertSessionHas(EnsureActiveCompany::SESSION_KEY, $logistik->id);

        $this->actingAs($multiUser)
            ->withSession([EnsureActiveCompany::SESSION_KEY => $logistik->id])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($logistik->name)
            ->assertSee('Finance');

        // Switching to PT Distribusi (NO membership) is strictly forbidden
        $forbiddenSwitch = $this->actingAs($multiUser)
            ->withSession([EnsureActiveCompany::SESSION_KEY => $logistik->id])
            ->post(route('active-company.switch'), [
                'company_id' => $distribusi->id,
            ]);

        $forbiddenSwitch->assertForbidden();
        $this->assertTrue(AuditLog::query()->where('action', 'company.switched')->exists());
    }
}
