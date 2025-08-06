<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\Company;
use App\Models\Role;
use App\Repositories\EloquentGroupRepository;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class GroupManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $groupRepository;
    protected $company;
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a company
        $this->company = Company::factory()->create([
            'name' => 'Test Company',
            'is_active' => true,
        ]);

        // Create a role
        $adminRole = Role::factory()->create([
            'name' => 'admin',
        ]);

        // Create an admin user
        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
            'role_id' => $adminRole->id,
            'user_type' => 'system_admin',
            'status' => 'active',
        ]);

        // Authenticate the user
        Auth::login($this->adminUser);

        // Create repository instance
        $this->groupRepository = new EloquentGroupRepository();
    }

    public function test_group_repository_can_create_group(): void
    {
        $groupData = [
            'name' => 'Test Group',
            'description' => 'A test group',
            'is_active' => true,
        ];

        $group = $this->groupRepository->create($groupData);

        $this->assertInstanceOf(Group::class, $group);
        $this->assertEquals('Test Group', $group->name);
        $this->assertEquals($this->company->id, $group->company_id);
        $this->assertEquals($this->adminUser->id, $group->created_by);
        $this->assertTrue($group->is_active);
    }

    public function test_group_repository_respects_tenant_scope(): void
    {
        // Create a group for the current company
        $group1 = $this->groupRepository->create([
            'name' => 'Company 1 Group',
            'description' => 'Group for company 1',
        ]);

        // Create another company and group
        $otherCompany = Company::factory()->create(['name' => 'Other Company']);
        $otherGroup = Group::factory()->create([
            'company_id' => $otherCompany->id,
            'name' => 'Company 2 Group',
        ]);

        // Repository should only return groups for the current tenant
        $groups = $this->groupRepository->all();
        
        $this->assertCount(1, $groups);
        $this->assertEquals('Company 1 Group', $groups->first()->name);
        $this->assertEquals($this->company->id, $groups->first()->company_id);
    }

    public function test_group_repository_can_count_groups(): void
    {
        // Create some groups
        $this->groupRepository->create(['name' => 'Group 1']);
        $this->groupRepository->create(['name' => 'Group 2']);
        $this->groupRepository->create(['name' => 'Group 3', 'is_active' => false]);

        $this->assertEquals(3, $this->groupRepository->count());
        $this->assertEquals(2, $this->groupRepository->activeCount());
        $this->assertEquals(1, $this->groupRepository->inactiveCount());
    }

    public function test_group_repository_can_search_by_name(): void
    {
        $this->groupRepository->create(['name' => 'Development Team']);
        $this->groupRepository->create(['name' => 'Marketing Team']);
        $this->groupRepository->create(['name' => 'Sales Department']);

        $results = $this->groupRepository->searchByName('Team');
        
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('name', 'Development Team'));
        $this->assertTrue($results->contains('name', 'Marketing Team'));
        $this->assertFalse($results->contains('name', 'Sales Department'));
    }

    public function test_group_repository_interface_is_bound(): void
    {
        $repository = app(GroupRepositoryInterface::class);
        
        $this->assertInstanceOf(EloquentGroupRepository::class, $repository);
    }
}
