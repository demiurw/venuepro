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
        // Initially, user should have onboarding_step_completed = 0
        $this->assertEquals(0, $this->systemAdmin->fresh()->onboarding_step_completed);
        
        $isComplete = $this->onboardingService->isOnboardingComplete($this->company->id);
        $this->assertFalse($isComplete);

        $progress = $this->onboardingService->getOnboardingProgress($this->company->id);
        
        $this->assertFalse($progress['buildings']);
        $this->assertFalse($progress['rooms']);
        $this->assertFalse($progress['groups']);
        $this->assertFalse($progress['users']);
        $this->assertFalse($progress['labels']);
        $this->assertEquals(0, $progress['current_step']);
        $this->assertEquals(1, $progress['next_step']);
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
        
        // Check that buildings were created
        $this->assertDatabaseHas('buildings', [
            'name' => 'Main Building',
            'company_id' => $this->company->id,
        ]);
        
        $this->assertDatabaseHas('buildings', [
            'name' => 'Annex Building',
            'company_id' => $this->company->id,
        ]);
        
        // Check that user's onboarding step was updated
        $this->assertEquals(1, $this->systemAdmin->fresh()->onboarding_step_completed);
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
                'capacity' => 10
            ]
        ];

        $result = $this->onboardingService->createRooms($this->systemAdmin, $roomsData);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, count($result['data']));
        
        $room = $result['data'][0];
        
        // Check that access control was created
        $this->assertDatabaseHas('access_control', [
            'group_id' => $group->id,
            'entity_id' => $room->id,
            'entity_type' => 'room',
            'access_level' => 'book',
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
        $response = $this->actingAs($this->systemAdmin)
            ->get('/admin/dashboard');

        // Should be redirected to onboarding buildings step
        $response->assertRedirect('/onboarding/buildings');
    }

    /** @test */
    public function complete_onboarding_flow_updates_steps_correctly()
    {
        // Step 1: Create Buildings
        $buildingsData = [
            [
                'name' => 'Main Office',
                'address' => '123 Business Ave, City, State 12345',
                'description' => 'Main company office building'
            ]
        ];

        $response = $this->actingAs($this->systemAdmin)
            ->post('/onboarding/buildings', ['buildings' => $buildingsData]);

        $response->assertRedirect('/onboarding/rooms');
        $this->assertEquals(1, $this->systemAdmin->fresh()->onboarding_step_completed);

        // Step 2: Create Rooms
        $building = $this->company->buildings()->first();
        $roomsData = [
            [
                'name' => 'Conference Room A',
                'building_id' => $building->id,
                'capacity' => 12,
                    'description' => 'Large conference room with projector',
                'equipment' => 'Projector, Whiteboard, Video Conferencing'
            ]
        ];

        $response = $this->actingAs($this->systemAdmin)
            ->post('/onboarding/rooms', ['rooms' => $roomsData]);

        $response->assertRedirect('/onboarding/groups');
        $this->assertEquals(2, $this->systemAdmin->fresh()->onboarding_step_completed);

        // Step 3: Create Groups
        $groupsData = [
            [
                'name' => 'Engineering Team',
                'description' => 'Software development and engineering team'
            ]
        ];

        $response = $this->actingAs($this->systemAdmin)
            ->post('/onboarding/groups', ['groups' => $groupsData]);

        $response->assertRedirect('/onboarding/users');
        $this->assertEquals(3, $this->systemAdmin->fresh()->onboarding_step_completed);

        // Step 4: Create Users
        $group = $this->company->groups()->first();
        $usersData = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@testcompany.com',
                'user_type' => 'booking_agent',
                'group_id' => $group->id
            ]
        ];

        $response = $this->actingAs($this->systemAdmin)
            ->post('/onboarding/users', ['users' => $usersData]);

        $response->assertRedirect('/onboarding/labels');
        $this->assertEquals(4, $this->systemAdmin->fresh()->onboarding_step_completed);

        // Step 5: Save Labels (Final Step)
        $labelsData = [
            [
                'name' => 'Team Meeting',
                'color' => '#3B82F6',
                'description' => 'Regular team meetings and standups'
            ],
            [
                'name' => 'Client Meeting',
                'color' => '#EF4444',
                'description' => 'Client presentations and consultations'
            ]
        ];

        $response = $this->actingAs($this->systemAdmin)
            ->post('/onboarding/labels', ['labels' => $labelsData]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertEquals(5, $this->systemAdmin->fresh()->onboarding_step_completed);

        // Verify onboarding is now complete
        $isComplete = $this->onboardingService->isOnboardingComplete($this->company->id);
        $this->assertTrue($isComplete);

        // Verify company has custom labels
        $this->assertNotNull($this->company->fresh()->custom_labels);
        $this->assertCount(2, $this->company->fresh()->custom_labels);

        // System admin should now be able to access dashboard
        $response = $this->actingAs($this->systemAdmin)
            ->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function onboarding_middleware_redirects_based_on_current_step()
    {
        // Step 0 (no steps completed) - redirect to buildings
        $response = $this->actingAs($this->systemAdmin)
            ->get('/admin/dashboard');
        $response->assertRedirect('/onboarding/buildings');

        // Complete step 1 (buildings)
        $this->systemAdmin->update(['onboarding_step_completed' => 1]);
        $response = $this->actingAs($this->systemAdmin)
            ->get('/admin/dashboard');
        $response->assertRedirect('/onboarding/rooms');

        // Complete step 2 (rooms)
        $this->systemAdmin->update(['onboarding_step_completed' => 2]);
        $response = $this->actingAs($this->systemAdmin)
            ->get('/admin/dashboard');
        $response->assertRedirect('/onboarding/groups');

        // Complete step 3 (groups)
        $this->systemAdmin->update(['onboarding_step_completed' => 3]);
        $response = $this->actingAs($this->systemAdmin)
            ->get('/admin/dashboard');
        $response->assertRedirect('/onboarding/users');

        // Complete step 4 (users)
        $this->systemAdmin->update(['onboarding_step_completed' => 4]);
        $response = $this->actingAs($this->systemAdmin)
            ->get('/admin/dashboard');
        $response->assertRedirect('/onboarding/labels');

        // Complete all steps (step 5)
        $this->systemAdmin->update(['onboarding_step_completed' => 5]);
        $response = $this->actingAs($this->systemAdmin)
            ->get('/admin/dashboard');
        $response->assertStatus(200); // Should allow access
    }

    /** @test */
    public function onboarding_skip_functionality_works()
    {
        // Test skipping buildings step
        $response = $this->actingAs($this->systemAdmin)
            ->post('/onboarding/skip/buildings');

        $response->assertRedirect('/onboarding/rooms');

        // Test skipping with incomplete prerequisites fails
        $response = $this->actingAs($this->systemAdmin)
            ->post('/onboarding/skip/users');

        $response->assertRedirect('/onboarding/buildings')
            ->assertSessionHas('error');
    }
}