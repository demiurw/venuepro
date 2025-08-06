<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private $company;
    private $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();

        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
            'user_type' => 'system_admin',
        ]);
    }

    /** @test */
    public function unauthenticated_users_are_redirected_to_login()
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function non_admin_users_are_forbidden()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'user_type' => 'invitee',
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    /** @test */
    public function admin_can_access_dashboard_and_see_correct_stats()
    {
        // Create some data for the company
        User::factory()->count(5)->create(['company_id' => $this->company->id]);
        Group::factory()->count(3)->create(['company_id' => $this->company->id, 'is_active' => true]);
        Group::factory()->count(2)->create(['company_id' => $this->company->id, 'is_active' => false]);

        // Create data for another company to ensure multi-tenancy
        $otherCompany = Company::factory()->create();
        User::factory()->count(2)->create(['company_id' => $otherCompany->id]);
        Group::factory()->count(4)->create(['company_id' => $otherCompany->id]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->has('stats.total_users')
            ->has('stats.total_groups')
            ->has('stats.active_groups')
            ->has('stats.inactive_groups')
            ->where('stats.total_users', 6) // 5 created + 1 admin
            ->where('stats.total_groups', 5)
            ->where('stats.active_groups', 3)
            ->where('stats.inactive_groups', 2)
        );
    }

    /** @test */
    public function admin_can_see_group_management_link_in_sidebar()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->has('auth.user.navigation_menu')
        );
    }
}
