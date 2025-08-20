<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\Company;
use App\Models\Building;
use App\Models\Room;
use App\Models\Group;
use App\Models\AccessControl;
use App\Services\OnboardingService;
use App\Enums\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OnboardingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OnboardingService $service;
    protected User $systemAdmin;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(OnboardingService::class);
        
        $this->company = Company::factory()->create();
        $this->systemAdmin = User::factory()->create([
            'user_type' => 'system_admin',
            'company_id' => $this->company->id,
            'onboarding_step_completed' => 0,
            'status' => UserStatus::ACTIVE,
        ]);
    }

    /** @test */
    public function it_detects_incomplete_onboarding_for_new_company()
    {
        $isComplete = $this->service->isOnboardingComplete($this->company->id);
        
        $this->assertFalse($isComplete);
    }

    /** @test */
    public function it_detects_complete_onboarding_when_user_completed_all_steps()
    {
        $this->systemAdmin->update(['onboarding_step_completed' => 5]);
        
        $isComplete = $this->service->isOnboardingComplete($this->company->id);
        
        $this->assertTrue($isComplete);
    }

    /** @test */
    public function it_returns_correct_progress_for_new_company()
    {
        $progress = $this->service->getOnboardingProgress($this->company->id);
        
        $this->assertFalse($progress['buildings']);
        $this->assertFalse($progress['rooms']);
        $this->assertFalse($progress['groups']);
        $this->assertFalse($progress['users']);
        $this->assertFalse($progress['labels']);
        $this->assertEquals(0, $progress['current_step']);
        $this->assertEquals(1, $progress['next_step']);
    }

    /** @test */
    public function it_returns_correct_progress_for_partially_completed_onboarding()
    {
        $this->systemAdmin->update(['onboarding_step_completed' => 3]);
        
        $progress = $this->service->getOnboardingProgress($this->company->id);
        
        $this->assertTrue($progress['buildings']);
        $this->assertTrue($progress['rooms']);
        $this->assertTrue($progress['groups']);
        $this->assertFalse($progress['users']);
        $this->assertFalse($progress['labels']);
        $this->assertEquals(3, $progress['current_step']);
        $this->assertEquals(4, $progress['next_step']);
    }

    /** @test */
    public function it_returns_correct_progress_for_completed_onboarding()
    {
        $this->systemAdmin->update(['onboarding_step_completed' => 5]);
        
        $progress = $this->service->getOnboardingProgress($this->company->id);
        
        $this->assertTrue($progress['buildings']);
        $this->assertTrue($progress['rooms']);
        $this->assertTrue($progress['groups']);
        $this->assertTrue($progress['users']);
        $this->assertTrue($progress['labels']);
        $this->assertEquals(5, $progress['current_step']);
        $this->assertNull($progress['next_step']);
    }

    /** @test */
    public function it_creates_buildings_successfully()
    {
        $buildingsData = [
            [
                'name' => 'Main Building',
                'address' => '123 Main St',
                'description' => 'Main office building'
            ],
            [
                'name' => 'Annex',
                'address' => '456 Side St',
                'description' => 'Secondary building'
            ]
        ];

        $result = $this->service->createBuildings($this->systemAdmin, $buildingsData);

        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['data']);
        $this->assertEquals('2 building(s) created successfully.', $result['message']);
        
        $this->assertDatabaseHas('buildings', [
            'name' => 'Main Building',
            'company_id' => $this->company->id
        ]);
        
        $this->assertDatabaseHas('buildings', [
            'name' => 'Annex',
            'company_id' => $this->company->id
        ]);
        
        // Check that user's onboarding step was updated
        $this->assertEquals(1, $this->systemAdmin->fresh()->onboarding_step_completed);
    }

    /** @test */
    public function it_creates_rooms_with_building_association()
    {
        $building = Building::factory()->create(['company_id' => $this->company->id]);
        
        $roomsData = [
            [
                'name' => 'Conference Room A',
                'building_id' => $building->id,
                'capacity' => 12
            ]
        ];

        $result = $this->service->createRooms($this->systemAdmin, $roomsData);

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['data']);
        
        $this->assertDatabaseHas('rooms', [
            'name' => 'Conference Room A',
            'building_id' => $building->id,
            'company_id' => $this->company->id,
            'capacity' => 12
        ]);
        
        // Check that user's onboarding step was updated
        $this->assertEquals(2, $this->systemAdmin->fresh()->onboarding_step_completed);
    }

    /** @test */
    public function it_creates_rooms_and_assigns_permissions_to_existing_groups()
    {
        $building = Building::factory()->create(['company_id' => $this->company->id]);
        $group = Group::factory()->create(['company_id' => $this->company->id]);
        
        $roomsData = [
            [
                'name' => 'Meeting Room',
                'building_id' => $building->id,
                'capacity' => 6
            ]
        ];

        $result = $this->service->createRooms($this->systemAdmin, $roomsData);

        $this->assertTrue($result['success']);
        
        $room = $result['data'][0];
        
        // Check that access control was created
        $this->assertDatabaseHas('access_control', [
            'group_id' => $group->id,
            'entity_id' => $room->id,
            'entity_type' => 'room',
            'access_level' => 'book',
            'company_id' => $this->company->id
        ]);
    }

    /** @test */
    public function it_creates_groups_successfully()
    {
        $groupsData = [
            [
                'name' => 'Engineering',
                'description' => 'Development team'
            ],
            [
                'name' => 'Marketing',
                'description' => 'Marketing team'
            ]
        ];

        $result = $this->service->createGroups($this->systemAdmin, $groupsData);

        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['data']);
        
        $this->assertDatabaseHas('groups', [
            'name' => 'Engineering',
            'company_id' => $this->company->id,
            'is_active' => true
        ]);
        
        // Check that user's onboarding step was updated
        $this->assertEquals(3, $this->systemAdmin->fresh()->onboarding_step_completed);
    }

    /** @test */
    public function it_creates_groups_and_assigns_existing_rooms()
    {
        $building = Building::factory()->create(['company_id' => $this->company->id]);
        $room = Room::factory()->create([
            'company_id' => $this->company->id,
            'building_id' => $building->id
        ]);
        
        $groupsData = [
            [
                'name' => 'Test Group',
                'description' => 'Test description'
            ]
        ];

        $result = $this->service->createGroups($this->systemAdmin, $groupsData);

        $this->assertTrue($result['success']);
        
        $group = $result['data'][0];
        
        // Check that access control was created for existing room
        $this->assertDatabaseHas('access_control', [
            'group_id' => $group->id,
            'entity_id' => $room->id,
            'entity_type' => 'room',
            'access_level' => 'book',
            'company_id' => $this->company->id
        ]);
    }

    /** @test */
    public function it_creates_users_with_correct_role_mapping()
    {
        $group = Group::factory()->create(['company_id' => $this->company->id]);
        
        $usersData = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@test.com',
                'user_type' => 'hod',
                'group_id' => $group->id
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane@test.com',
                'user_type' => 'booking_agent',
                'group_id' => $group->id
            ],
            [
                'first_name' => 'Bob',
                'last_name' => 'Wilson',
                'email' => 'bob@test.com',
                'user_type' => 'invitee',
                'group_id' => null
            ]
        ];

        $result = $this->service->createUsers($this->systemAdmin, $usersData);

        $this->assertTrue($result['success']);
        $this->assertCount(3, $result['data']);
        
        // Check HOD user (role_id = 1)
        $this->assertDatabaseHas('users', [
            'email' => 'john@test.com',
            'user_type' => 'hod',
            'role_id' => 1,
            'group_id' => $group->id,
            'status' => UserStatus::PENDING
        ]);
        
        // Check booking agent
        $bookingAgentRole = \Spatie\Permission\Models\Role::where('name', 'Booking Agent')->first();
        $this->assertDatabaseHas('users', [
            'email' => 'jane@test.com',
            'user_type' => 'booking_agent',
            'role_id' => $bookingAgentRole->id,
            'group_id' => $group->id
        ]);
        
        // Check invitee (role_id = 3)
        $this->assertDatabaseHas('users', [
            'email' => 'bob@test.com',
            'user_type' => 'invitee',
            'role_id' => 3,
            'group_id' => null
        ]);
        
        // Check that user's onboarding step was updated
        $this->assertEquals(4, $this->systemAdmin->fresh()->onboarding_step_completed);
    }

    /** @test */
    public function it_saves_company_labels_and_completes_onboarding()
    {
        $labelsData = [
            [
                'name' => 'Team Meeting',
                'color' => '#3B82F6',
                'description' => 'Regular team meetings'
            ],
            [
                'name' => 'Client Call',
                'color' => '#EF4444',
                'description' => 'Client meetings'
            ]
        ];

        $result = $this->service->saveCompanyLabels($this->systemAdmin, $labelsData);

        $this->assertTrue($result['success']);
        $this->assertStringContains('Onboarding completed!', $result['message']);
        
        // Check that company has custom labels
        $company = $this->company->fresh();
        $this->assertNotNull($company->custom_labels);
        $this->assertCount(2, $company->custom_labels);
        $this->assertEquals('Team Meeting', $company->custom_labels[0]['name']);
        $this->assertEquals('#3B82F6', $company->custom_labels[0]['color']);
        
        // Check that user's onboarding step was updated to completed
        $this->assertEquals(5, $this->systemAdmin->fresh()->onboarding_step_completed);
    }

    /** @test */
    public function it_gets_available_buildings()
    {
        Building::factory()->count(3)->create(['company_id' => $this->company->id]);
        
        $buildings = $this->service->getAvailableBuildings($this->company->id);
        
        $this->assertCount(3, $buildings);
        $this->assertArrayHasKey('id', $buildings[0]);
        $this->assertArrayHasKey('name', $buildings[0]);
        $this->assertArrayHasKey('address', $buildings[0]);
    }

    /** @test */
    public function it_gets_available_groups()
    {
        Group::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'is_active' => true
        ]);
        
        // Create inactive group that should not be returned
        Group::factory()->create([
            'company_id' => $this->company->id,
            'is_active' => false
        ]);
        
        $groups = $this->service->getAvailableGroups($this->company->id);
        
        $this->assertCount(2, $groups);
        $this->assertArrayHasKey('id', $groups[0]);
        $this->assertArrayHasKey('name', $groups[0]);
        $this->assertArrayHasKey('description', $groups[0]);
    }

    /** @test */
    public function it_handles_database_errors_gracefully()
    {
        // Simulate database error by closing connection
        DB::disconnect();
        
        $buildingsData = [['name' => 'Test', 'address' => 'Test Address']];
        
        $result = $this->service->createBuildings($this->systemAdmin, $buildingsData);
        
        $this->assertFalse($result['success']);
        $this->assertStringContains('Failed to create buildings', $result['message']);
    }

    /** @test */
    public function it_handles_missing_company_gracefully()
    {
        $nonExistentCompanyId = 99999;
        
        $isComplete = $this->service->isOnboardingComplete($nonExistentCompanyId);
        $progress = $this->service->getOnboardingProgress($nonExistentCompanyId);
        
        $this->assertFalse($isComplete);
        $this->assertFalse($progress['buildings']);
        $this->assertEquals(0, $progress['current_step']);
    }
}