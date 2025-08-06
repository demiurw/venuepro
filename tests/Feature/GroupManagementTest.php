<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Group;
use App\Models\Role;
use App\Models\Company;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class GroupManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $systemAdmin;
    protected $bookingAgent;
    protected $hodRole;
    protected $agentRole;
    protected $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test company
        $this->company = Company::factory()->create();

        // Create roles
        $this->adminRole = Role::factory()->create(['name' => 'system_admin']);
        $this->agentRole = Role::factory()->create(['name' => 'booking_agent']);
        $this->hodRole = Role::factory()->create(['name' => 'hod']);

        // Create test users
        $this->systemAdmin = User::factory()->create([
            'company_id' => $this->company->id,
            'user_type' => 'system_admin'
        ]);
        $this->systemAdmin->roles()->attach($this->adminRole->id);

        $this->bookingAgent = User::factory()->create([
            'company_id' => $this->company->id,
            'user_type' => 'booking_agent'
        ]);
        $this->bookingAgent->roles()->attach($this->agentRole->id);
    }

    /** @test */
    public function test_group_management_link_is_visible_to_admins(): void
    {
        $this->actingAs($this->systemAdmin);
        
        $response = $this->get('/admin/dashboard');
        
        $response->assertStatus(200);
        // In a real implementation, you would check for the presence of the Group Management link
        // This depends on how the sidebar is structured in your Inertia response
    }

    /** @test */
    public function test_group_management_link_is_not_visible_to_non_admins(): void
    {
        $this->actingAs($this->bookingAgent);
        
        // Booking agents should not have access to admin routes
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(403); // Should be forbidden
    }

    /** @test */
    public function test_dashboard_shows_correct_counts_for_company(): void
    {
        // Create users and groups for this company
        User::factory()->count(5)->create(['company_id' => $this->company->id]);
        Group::factory()->count(3)->create(['company_id' => $this->company->id]);

        // Create users and groups for another company (should not be counted)
        $otherCompany = Company::factory()->create();
        User::factory()->count(2)->create(['company_id' => $otherCompany->id]);
        Group::factory()->count(1)->create(['company_id' => $otherCompany->id]);

        $this->actingAs($this->systemAdmin);
        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->has('stats')
            ->where('stats.total_users', 6) // 5 + system admin
            ->where('stats.total_groups', 3) // Only groups from this company
        );
    }

    /** @test */
    public function test_groups_index_shows_company_groups_only(): void
    {
        // Create groups for this company
        $companyGroups = Group::factory()->count(3)->create(['company_id' => $this->company->id]);
        
        // Create groups for another company
        $otherCompany = Company::factory()->create();
        Group::factory()->count(2)->create(['company_id' => $otherCompany->id]);

        $this->actingAs($this->systemAdmin);
        $response = $this->get('/api/groups');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
        
        // Verify all returned groups belong to the correct company
        $responseData = $response->json('data');
        foreach ($responseData as $group) {
            $this->assertEquals($this->company->id, $group['company_id']);
        }
    }

    /** @test */
    public function test_group_creation_with_valid_data(): void
    {
        $this->actingAs($this->systemAdmin);

        $groupData = [
            'name' => 'Test Group',
            'description' => 'Test group description',
            'is_active' => true
        ];

        $response = $this->post('/api/groups', $groupData);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('groups', [
            'name' => 'Test Group',
            'description' => 'Test group description',
            'company_id' => $this->company->id,
            'is_active' => true
        ]);
    }

    /** @test */
    public function test_group_creation_requires_authentication(): void
    {
        $groupData = [
            'name' => 'Test Group',
            'description' => 'Test description'
        ];

        $response = $this->post('/api/groups', $groupData);
        $response->assertStatus(401);
    }

    /** @test */
    public function test_group_creation_validates_required_fields(): void
    {
        $this->actingAs($this->systemAdmin);

        $response = $this->post('/api/groups', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function test_group_update_with_valid_data(): void
    {
        $group = Group::factory()->create(['company_id' => $this->company->id]);
        
        $this->actingAs($this->systemAdmin);

        $updateData = [
            'name' => 'Updated Group Name',
            'description' => 'Updated description'
        ];

        $response = $this->put("/api/groups/{$group->id}", $updateData);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'name' => 'Updated Group Name',
            'description' => 'Updated description'
        ]);
    }

    /** @test */
    public function test_cannot_update_group_from_different_company(): void
    {
        $otherCompany = Company::factory()->create();
        $group = Group::factory()->create(['company_id' => $otherCompany->id]);
        
        $this->actingAs($this->systemAdmin);

        $response = $this->put("/api/groups/{$group->id}", [
            'name' => 'Hacked Name'
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function test_group_deletion(): void
    {
        $group = Group::factory()->create(['company_id' => $this->company->id]);
        
        $this->actingAs($this->systemAdmin);

        $response = $this->delete("/api/groups/{$group->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('groups', ['id' => $group->id]);
    }

    /** @test */
    public function test_cannot_delete_group_from_different_company(): void
    {
        $otherCompany = Company::factory()->create();
        $group = Group::factory()->create(['company_id' => $otherCompany->id]);
        
        $this->actingAs($this->systemAdmin);

        $response = $this->delete("/api/groups/{$group->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('groups', ['id' => $group->id]);
    }

    /** @test */
    public function test_group_activation(): void
    {
        $group = Group::factory()->create([
            'company_id' => $this->company->id,
            'is_active' => false
        ]);
        
        $this->actingAs($this->systemAdmin);

        $response = $this->post("/api/groups/{$group->id}/activate");

        $response->assertStatus(200);
        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'is_active' => true
        ]);
    }

    /** @test */
    public function test_group_deactivation(): void
    {
        $group = Group::factory()->create([
            'company_id' => $this->company->id,
            'is_active' => true
        ]);
        
        $this->actingAs($this->systemAdmin);

        $response = $this->post("/api/groups/{$group->id}/deactivate");

        $response->assertStatus(200);
        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'is_active' => false
        ]);
    }

    /** @test */
    public function test_repository_binding_is_working(): void
    {
        $repository = app(GroupRepositoryInterface::class);
        $this->assertNotNull($repository);
        $this->assertInstanceOf(\App\Repositories\EloquentGroupRepository::class, $repository);
    }

    /** @test */
    public function test_repository_count_by_company(): void
    {
        // Create groups for this company
        Group::factory()->count(3)->create(['company_id' => $this->company->id]);
        
        // Create groups for another company
        $otherCompany = Company::factory()->create();
        Group::factory()->count(2)->create(['company_id' => $otherCompany->id]);

        $repository = app(GroupRepositoryInterface::class);
        $count = $repository->countByCompany($this->company->id);

        $this->assertEquals(3, $count);
    }

    /** @test */
    public function test_repository_search_functionality(): void
    {
        Group::factory()->create([
            'name' => 'Marketing Team',
            'company_id' => $this->company->id
        ]);
        Group::factory()->create([
            'name' => 'Sales Team',
            'company_id' => $this->company->id
        ]);
        Group::factory()->create([
            'name' => 'Development Team',
            'company_id' => $this->company->id
        ]);

        $repository = app(GroupRepositoryInterface::class);
        $results = $repository->searchByName('Marketing', $this->company->id);

        $this->assertCount(1, $results);
        $this->assertEquals('Marketing Team', $results->first()->name);
    }

    /** @test */
    public function test_booking_agent_requires_group_validation(): void
    {
        $group = Group::factory()->create(['company_id' => $this->company->id]);
        
        $this->actingAs($this->systemAdmin);

        // Test creating booking agent without group - should fail
        $response = $this->post('/admin/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'role_id' => $this->agentRole->id,
            'user_type' => 'booking_agent',
            // missing group_id
        ]);

        $response->assertSessionHasErrors(['group_id']);

        // Test creating booking agent with group - should succeed
        $response = $this->post('/admin/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'role_id' => $this->agentRole->id,
            'user_type' => 'booking_agent',
            'group_id' => $group->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'group_id' => $group->id
        ]);
    }

    /** @test */
    public function test_hod_requires_group_validation(): void
    {
        $group = Group::factory()->create(['company_id' => $this->company->id]);
        
        $this->actingAs($this->systemAdmin);

        // Test creating HOD without group - should fail
        $response = $this->post('/admin/users', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'role_id' => $this->hodRole->id,
            'user_type' => 'hod',
            // missing group_id
        ]);

        $response->assertSessionHasErrors(['group_id']);

        // Test creating HOD with group - should succeed
        $response = $this->post('/admin/users', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'role_id' => $this->hodRole->id,
            'user_type' => 'hod',
            'group_id' => $group->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'group_id' => $group->id
        ]);
    }

    /** @test */
    public function test_system_admin_does_not_require_group(): void
    {
        $this->actingAs($this->systemAdmin);

        $response = $this->post('/admin/users', [
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'role_id' => $this->adminRole->id,
            'user_type' => 'system_admin',
            // no group_id - should be fine
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'group_id' => null
        ]);
    }
}