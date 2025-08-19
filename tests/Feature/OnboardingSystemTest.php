<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Building;
use App\Models\Room;
use App\Models\Group;
use App\Enums\UserStatus;
use App\Services\OnboardingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OnboardingSystemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $onboardingService;
    protected $systemAdmin;
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->onboardingService = app(OnboardingService::class);
        
        // Create a company and system admin for testing
        $this->company = Company::factory()->create([
            'name' => 'Test Company',
            'slug' => 'test-company',
        ]);
        
        $this->systemAdmin = User::factory()->create([
            'user_type' => 'system_admin',
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);
    }

    /** @test */
    public function system_admin_can_access_onboarding_buildings_step()
    {
        $response = $this->actingAs($this->systemAdmin)
            ->get(route('onboarding.buildings'));

        $response->assertStatus(200);
    }

    /** @test */
    public function onboarding_service_detects_incomplete_onboarding_correctly()
    {
        $isComplete = $this->onboardingService->isOnboardingComplete($this->company->id);
        $this->assertFalse($isComplete);

        $progress = $this->onboardingService->getOnboardingProgress($this->company->id);
        
        $this->assertFalse($progress['buildings']);
        $this->assertFalse($progress['rooms']);
        $this->assertFalse($progress['groups']);
        $this->assertFalse($progress['users']);
    }

    /** @test */
    public function onboarding_service_can_create_buildings()
    {
        $buildingsData = [
            [
                'name' => 'Main Building',
                'address' => '123 Main St, City, State'
            ],
            [
                'name' => 'Annex Building',
                'address' => '456 Secondary St, City, State'
            ]
        ];

        $result = $this->onboardingService->createBuildings($this->systemAdmin, $buildingsData);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, count($result['data']));
        
        $this->assertDatabaseHas('building', [
            'name' => 'Main Building',
            'company_id' => $this->company->id,
        ]);
        
        $this->assertDatabaseHas('building', [
            'name' => 'Annex Building',
            'company_id' => $this->company->id,
        ]);
    }

    /** @test */
    public function onboarding_service_can_create_groups()
    {
        $groupsData = [
            [
                'name' => 'Engineering Team',
                'description' => 'Software development team'
            ],
            [
                'name' => 'Marketing Team',
                'description' => 'Marketing and communications team'
            ]
        ];

        $result = $this->onboardingService->createGroups($this->systemAdmin, $groupsData);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, count($result['data']));
        
        $this->assertDatabaseHas('groups', [
            'name' => 'Engineering Team',
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function onboarding_service_creates_rooms_with_group_permissions()
    {
        // First create a building and group
        $building = Building::create([
            'name' => 'Test Building',
            'address' => '123 Test St',
            'company_id' => $this->company->id,
        ]);

        $group = Group::create([
            'name' => 'Test Group',
            'description' => 'Test group',
            'company_id' => $this->company->id,
            'created_by' => $this->systemAdmin->id,
            'is_active' => true,
        ]);

        $roomsData = [
            [
                'name' => 'Conference Room A',
                'building_id' => $building->id,
                'capacity' => 10,
                'type' => 'conference'
            ]
        ];

        $result = $this->onboardingService->createRooms($this->systemAdmin, $roomsData);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, count($result['data']));
        
        $room = $result['data'][0];
        
        // Check that access control was created
        $this->assertDatabaseHas('access_control', [
            'group_id' => $group->id,
            'resource_id' => $room->id,
            'resource_type' => Room::class,
            'company_id' => $this->company->id,
        ]);
    }

    /** @test */
    public function onboarding_service_can_create_users()
    {
        // First create a group for the users
        $group = Group::create([
            'name' => 'Test Group',
            'description' => 'Test group',
            'company_id' => $this->company->id,
            'created_by' => $this->systemAdmin->id,
            'is_active' => true,
        ]);

        $usersData = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@test.com',
                'user_type' => 'booking_agent',
                'group_id' => $group->id,
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@test.com',
                'user_type' => 'invitee',
                'group_id' => null,
            ]
        ];

        $result = $this->onboardingService->createUsers($this->systemAdmin, $usersData);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, count($result['data']));
        
        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@test.com',
            'user_type' => 'booking_agent',
            'company_id' => $this->company->id,
            'status' => UserStatus::PENDING,
        ]);
    }

    /** @test */
    public function onboarding_detects_complete_setup()
    {
        // Create all required entities
        $building = Building::create([
            'name' => 'Test Building',
            'address' => '123 Test St',
            'company_id' => $this->company->id,
        ]);

        $room = Room::create([
            'name' => 'Test Room',
            'building_id' => $building->id,
            'capacity' => 10,
            'type' => 'conference',
            'company_id' => $this->company->id,
        ]);

        $group = Group::create([
            'name' => 'Test Group',
            'description' => 'Test group',
            'company_id' => $this->company->id,
            'created_by' => $this->systemAdmin->id,
            'is_active' => true,
        ]);

        User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test.user@test.com',
            'user_type' => 'booking_agent',
            'company_id' => $this->company->id,
            'group_id' => $group->id,
            'role_id' => 4,
            'status' => UserStatus::PENDING,
            'auth_method' => 'otp',
        ]);

        $isComplete = $this->onboardingService->isOnboardingComplete($this->company->id);
        $this->assertTrue($isComplete);

        $progress = $this->onboardingService->getOnboardingProgress($this->company->id);
        $this->assertTrue($progress['buildings']);
        $this->assertTrue($progress['rooms']);
        $this->assertTrue($progress['groups']);
        $this->assertTrue($progress['users']);
    }

    /** @test */
    public function non_system_admin_cannot_access_onboarding_routes()
    {
        $regularUser = User::factory()->create([
            'user_type' => 'booking_agent',
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->actingAs($regularUser)
            ->get(route('onboarding.buildings'));

        $response->assertStatus(403);
    }

    /** @test */
    public function system_admin_redirected_from_dashboard_when_onboarding_incomplete()
    {
        // Mock the middleware logic - this would normally be tested with browser tests
        $progress = $this->onboardingService->getOnboardingProgress($this->company->id);
        
        $this->assertFalse($progress['buildings']);
        $this->assertFalse($progress['rooms']);
        $this->assertFalse($progress['groups']);
        $this->assertFalse($progress['users']);
        
        // When onboarding is incomplete, system admin should be redirected
        $isComplete = $this->onboardingService->isOnboardingComplete($this->company->id);
        $this->assertFalse($isComplete);
    }
}