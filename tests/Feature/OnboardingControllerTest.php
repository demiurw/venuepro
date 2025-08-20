<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Building;
use App\Models\Room;
use App\Models\Group;
use App\Enums\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OnboardingControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $systemAdmin;
    protected User $regularUser;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::factory()->create();
        
        $this->systemAdmin = User::factory()->create([
            'user_type' => 'system_admin',
            'company_id' => $this->company->id,
            'onboarding_step_completed' => 0,
            'status' => UserStatus::ACTIVE,
        ]);
        
        $this->regularUser = User::factory()->create([
            'user_type' => 'booking_agent',
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);
    }

    /** @test */
    public function buildings_step_renders_correctly_for_system_admin()
    {
        $response = $this->actingAs($this->systemAdmin)
            ->get('/onboarding/buildings');

        $response
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Onboarding/Index')
                ->has('progress')
                ->where('currentStep', 1)
            );
    }

    /** @test */
    public function rooms_step_renders_with_buildings_data()
    {
        // Create some buildings first
        Building::factory()->count(2)->create(['company_id' => $this->company->id]);
        
        $response = $this->actingAs($this->systemAdmin)
            ->get('/onboarding/rooms');

        $response
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Onboarding/Index')
                ->where('currentStep', 2)
                ->has('buildings', 2)
                ->has('buildings.0.id')
                ->has('buildings.0.name')
                ->has('buildings.0.address')
            );
    }

    /** @test */
    public function groups_step_renders_correctly()
    {
        $response = $this->actingAs($this->systemAdmin)
            ->get('/onboarding/groups');

        $response
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Onboarding/Index')
                ->where('currentStep', 3)
                ->has('progress')
            );
    }

    /** @test */
    public function users_step_renders_with_groups_data()
    {
        // Create some groups first
        Group::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'is_active' => true
        ]);
        
        $response = $this->actingAs($this->systemAdmin)
            ->get('/onboarding/users');

        $response
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Onboarding/Index')
                ->where('currentStep', 4)
                ->has('groups', 3)
                ->has('groups.0.id')
                ->has('groups.0.name')
            );
    }

    /** @test */
    public function labels_step_renders_correctly()
    {
        $response = $this->actingAs($this->systemAdmin)
            ->get('/onboarding/labels');

        $response
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Onboarding/Index')
                ->where('currentStep', 5)
                ->has('progress')
            );
    }

    /** @test */
    public function non_system_admin_cannot_access_onboarding_routes()
    {
        $routes = [
            '/onboarding/buildings',
            '/onboarding/rooms',
            '/onboarding/groups',
            '/onboarding/users',
            '/onboarding/labels'
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->regularUser)->get($route);
            $response->assertStatus(403);
        }
    }

    /** @test */
    public function unauthenticated_user_is_redirected()
    {
        $response = $this->get('/onboarding/buildings');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function buildings_can_be_created_successfully()
    {
        $buildingsData = [
            'buildings' => [
                [
                    'name' => 'Main Office',
                    'address' => '123 Business Ave, City, State 12345',
                    'description' => 'Primary office location'
                ],
                [
                    'name' => 'Warehouse',
                    'address' => '456 Industrial Dr, City, State 54321',
                    'description' => 'Storage and logistics center'
                ]
            ]
        ];

        $response = $this->actingAs($this->systemAdmin)
            ->post('/onboarding/buildings', $buildingsData);

        $response->assertRedirect('/onboarding/rooms');
        
        $this->assertDatabaseHas('buildings', [
            'name' => 'Main Office',
            'company_id' => $this->company->id
        ]);
        
        $this->assertDatabaseHas('buildings', [
            'name' => 'Warehouse',
            'company_id' => $this->company->id
        ]);

        // Check that user's step was updated
        $this->assertEquals(1, $this->systemAdmin->fresh()->onboarding_step_completed);
    }

    /** @test */
    public function buildings_creation_fails_with_invalid_data()
    {
        $invalidData = [
            'buildings' => [
                [
                    'name' => '', // Empty name should fail
                    'address' => '123 Test St'
                ]
            ]
        ];

        $response = $this->actingAs($this->systemAdmin)
            ->post('/onboarding/buildings', $invalidData);

        $response->assertSessionHasErrors();
        $this->assertEquals(0, $this->systemAdmin->fresh()->onboarding_step_completed);
    }

    /** @test */
    public function rooms_can_be_created_with_building_association()
    {
        $building = Building::factory()->create(['company_id' => $this->company->id]);
        
        $roomsData = [
            'rooms' => [
                [
                    'name' => 'Conference Room A',
                    'building_id' => $building->id,
                    'capacity' => 12,
                    'type' => 'conference',
                    'description' => 'Large meeting room with AV equipment',
                    'equipment' => 'Projector, Whiteboard, Video Conferencing'
                ]
            ]
        ];

        $response = $this->actingAs($this->systemAdmin)
            ->post('/onboarding/rooms', $roomsData);

        $response->assertRedirect('/onboarding/groups');
        
        $this->assertDatabaseHas('rooms', [
            'name' => 'Conference Room A',
            'building_id' => $building->id,
            'company_id' => $this->company->id,
            'capacity' => 12,
            'type' => 'conference'
        ]);

        $this->assertEquals(2, $this->systemAdmin->fresh()->onboarding_step_completed);
    }

    /** @test */
    public function groups_can_be_created_successfully()
    {
        $groupsData = [
            'groups' => [
                [
                    'name' => 'Engineering Team',
                    'description' => 'Software development and technical team'
                ],
                [
                    'name' => 'Marketing Department',
                    'description' => 'Marketing, communications, and brand management'
                ]
            ]
        ];

        $response = $this->actingAs($this->systemAdmin)
            ->post('/onboarding/groups', $groupsData);

        $response->assertRedirect('/onboarding/users');
        
        $this->assertDatabaseHas('groups', [
            'name' => 'Engineering Team',
            'company_id' => $this->company->id,
            'is_active' => true
        ]);

        $this->assertDatabaseHas('groups', [
            'name' => 'Marketing Department',
            'company_id' => $this->company->id,
            'is_active' => true
        ]);

        $this->assertEquals(3, $this->systemAdmin->fresh()->onboarding_step_completed);
    }

    /** @test */
    public function users_can_be_created_with_group_assignment()
    {
        $group = Group::factory()->create(['company_id' => $this->company->id]);
        
        $usersData = [
            'users' => [
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john.doe@company.com',
                    'user_type' => 'booking_agent',
                    'group_id' => $group->id
                ],
                [
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'email' => 'jane.smith@company.com',
                    'user_type' => 'invitee',
                    'group_id' => null
                ]
            ]
        ];

        $response = $this->actingAs($this->systemAdmin)
            ->post('/onboarding/users', $usersData);

        $response->assertRedirect('/onboarding/labels');
        
        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@company.com',
            'user_type' => 'booking_agent',
            'company_id' => $this->company->id,
            'group_id' => $group->id,
            'status' => UserStatus::PENDING
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane.smith@company.com',
            'user_type' => 'invitee',
            'company_id' => $this->company->id,
            'group_id' => null
        ]);

        $this->assertEquals(4, $this->systemAdmin->fresh()->onboarding_step_completed);
    }

    /** @test */
    public function labels_can_be_saved_and_onboarding_completed()
    {
        $labelsData = [
            'labels' => [
                [
                    'name' => 'Team Meeting',
                    'color' => '#3B82F6',
                    'description' => 'Regular team meetings and standups'
                ],
                [
                    'name' => 'Client Meeting',
                    'color' => '#EF4444',
                    'description' => 'External client meetings and presentations'
                ],
                [
                    'name' => 'Training Session',
                    'color' => '#10B981',
                    'description' => 'Employee training and development sessions'
                ]
            ]
        ];

        $response = $this->actingAs($this->systemAdmin)
            ->post('/onboarding/labels', $labelsData);

        $response->assertRedirect('/admin/dashboard');
        
        // Check that labels were saved to company
        $company = $this->company->fresh();
        $this->assertNotNull($company->custom_labels);
        $this->assertCount(3, $company->custom_labels);
        $this->assertEquals('Team Meeting', $company->custom_labels[0]['name']);
        $this->assertEquals('#3B82F6', $company->custom_labels[0]['color']);

        // Check that onboarding is marked as complete
        $this->assertEquals(5, $this->systemAdmin->fresh()->onboarding_step_completed);
    }

    /** @test */
    public function skip_functionality_works_correctly()
    {
        $response = $this->actingAs($this->systemAdmin)
            ->post('/onboarding/skip/buildings');

        $response->assertRedirect('/onboarding/rooms');
    }

    /** @test */
    public function skip_fails_when_prerequisites_not_met()
    {
        // Try to skip users step without completing previous steps
        $response = $this->actingAs($this->systemAdmin)
            ->post('/onboarding/skip/users');

        $response->assertRedirect('/onboarding/buildings')
            ->assertSessionHas('error');
    }

    /** @test */
    public function previous_step_navigation_works()
    {
        $response = $this->actingAs($this->systemAdmin)
            ->post('/onboarding/previous/rooms');

        $response->assertRedirect('/onboarding/buildings');
    }

    /** @test */
    public function progress_endpoint_returns_correct_data()
    {
        // Set user to step 3 completed
        $this->systemAdmin->update(['onboarding_step_completed' => 3]);

        $response = $this->actingAs($this->systemAdmin)
            ->get('/onboarding/progress');

        $response
            ->assertStatus(200)
            ->assertJson([
                'progress' => [
                    'buildings' => true,
                    'rooms' => true,
                    'groups' => true,
                    'users' => false,
                    'labels' => false,
                    'current_step' => 3,
                    'next_step' => 4
                ],
                'isComplete' => false
            ]);
    }

    /** @test */
    public function controller_methods_redirect_if_prerequisites_not_met()
    {
        // Try to access rooms step without completing buildings
        $response = $this->actingAs($this->systemAdmin)
            ->get('/onboarding/rooms');

        $response->assertRedirect('/onboarding/buildings')
            ->assertSessionHas('error');
    }

    /** @test */
    public function step_validation_prevents_out_of_order_completion()
    {
        // Try to complete groups without doing buildings and rooms first
        $groupsData = [
            'groups' => [
                [
                    'name' => 'Test Group',
                    'description' => 'Test description'
                ]
            ]
        ];

        $response = $this->actingAs($this->systemAdmin)
            ->post('/onboarding/groups', $groupsData);

        // Should redirect to earlier step
        $response->assertRedirect('/onboarding/buildings')
            ->assertSessionHas('error');
    }

    /** @test */
    public function completed_onboarding_allows_dashboard_access()
    {
        $this->systemAdmin->update(['onboarding_step_completed' => 5]);

        $response = $this->actingAs($this->systemAdmin)
            ->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function incomplete_onboarding_redirects_from_dashboard()
    {
        // User with incomplete onboarding should be redirected
        $this->systemAdmin->update(['onboarding_step_completed' => 2]);

        $response = $this->actingAs($this->systemAdmin)
            ->get('/admin/dashboard');

        $response->assertRedirect('/onboarding/groups');
    }

    /** @test */
    public function error_handling_works_for_invalid_step_data()
    {
        $invalidBuildingsData = [
            'buildings' => [
                [
                    'name' => str_repeat('a', 300), // Too long
                    'address' => '' // Required field missing
                ]
            ]
        ];

        $response = $this->actingAs($this->systemAdmin)
            ->post('/onboarding/buildings', $invalidBuildingsData);

        $response->assertSessionHasErrors();
        $this->assertEquals(0, $this->systemAdmin->fresh()->onboarding_step_completed);
    }
}