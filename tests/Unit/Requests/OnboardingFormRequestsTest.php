<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Onboarding\CreateBuildingsRequest;
use App\Http\Requests\Onboarding\CreateRoomsRequest;
use App\Http\Requests\Onboarding\CreateGroupsRequest;
use App\Http\Requests\Onboarding\CreateUsersRequest;
use App\Http\Requests\Onboarding\SaveLabelsRequest;
use App\Models\User;
use App\Models\Company;
use App\Models\Building;
use App\Models\Group;
use App\Enums\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class OnboardingFormRequestsTest extends TestCase
{
    use RefreshDatabase;

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
            'status' => UserStatus::ACTIVE,
        ]);
        
        $this->regularUser = User::factory()->create([
            'user_type' => 'booking_agent',
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);
    }

    /** @test */
    public function create_buildings_request_authorizes_system_admin_only()
    {
        $request = new CreateBuildingsRequest();
        
        // Test system admin authorization
        $this->be($this->systemAdmin);
        $this->assertTrue($request->authorize());
        
        // Test regular user authorization
        $this->be($this->regularUser);
        $this->assertFalse($request->authorize());
        
        // Test unauthenticated user
        auth()->logout();
        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function create_buildings_request_validates_correctly()
    {
        $request = new CreateBuildingsRequest();
        $rules = $request->rules();

        // Test valid data
        $validData = [
            'buildings' => [
                [
                    'name' => 'Main Building',
                    'address' => '123 Main St, City, State 12345',
                    'description' => 'Main office building'
                ],
                [
                    'name' => 'Annex',
                    'address' => '456 Side St, City, State 54321'
                ]
            ]
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // Test missing required fields
        $invalidData = [
            'buildings' => [
                [
                    'name' => '',
                    'address' => '',
                ]
            ]
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('buildings.0.name', $validator->errors()->toArray());
        $this->assertArrayHasKey('buildings.0.address', $validator->errors()->toArray());

        // Test too many buildings
        $tooManyBuildings = [
            'buildings' => array_fill(0, 11, ['name' => 'Building', 'address' => 'Address'])
        ];

        $validator = Validator::make($tooManyBuildings, $rules);
        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function create_rooms_request_validates_correctly()
    {
        $building = Building::factory()->create(['company_id' => $this->company->id]);
        
        $request = new CreateRoomsRequest();
        $rules = $request->rules();

        // Test valid data
        $validData = [
            'rooms' => [
                [
                    'name' => 'Conference Room A',
                    'building_id' => $building->id,
                    'capacity' => 12,
                    'type' => 'conference',
                    'description' => 'Large conference room',
                    'equipment' => 'Projector, Whiteboard'
                ]
            ]
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // Test invalid room type
        $invalidTypeData = [
            'rooms' => [
                [
                    'name' => 'Test Room',
                    'building_id' => $building->id,
                    'capacity' => 10,
                    'type' => 'invalid_type'
                ]
            ]
        ];

        $validator = Validator::make($invalidTypeData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('rooms.0.type', $validator->errors()->toArray());

        // Test invalid capacity
        $invalidCapacityData = [
            'rooms' => [
                [
                    'name' => 'Test Room',
                    'building_id' => $building->id,
                    'capacity' => 0,
                    'type' => 'conference'
                ]
            ]
        ];

        $validator = Validator::make($invalidCapacityData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('rooms.0.capacity', $validator->errors()->toArray());
    }

    /** @test */
    public function create_groups_request_validates_correctly()
    {
        $request = new CreateGroupsRequest();
        $rules = $request->rules();

        // Test valid data
        $validData = [
            'groups' => [
                [
                    'name' => 'Engineering Team',
                    'description' => 'Software development team'
                ],
                [
                    'name' => 'Marketing',
                    'description' => 'Marketing and communications'
                ]
            ]
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // Test missing name
        $invalidData = [
            'groups' => [
                [
                    'name' => '',
                    'description' => 'Test description'
                ]
            ]
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('groups.0.name', $validator->errors()->toArray());

        // Test too many groups
        $tooManyGroups = [
            'groups' => array_fill(0, 16, ['name' => 'Group', 'description' => 'Description'])
        ];

        $validator = Validator::make($tooManyGroups, $rules);
        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function create_users_request_validates_correctly()
    {
        $group = Group::factory()->create(['company_id' => $this->company->id]);
        
        $request = new CreateUsersRequest();
        $rules = $request->rules();

        // Test valid data
        $validData = [
            'users' => [
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john.doe@test.com',
                    'user_type' => 'booking_agent',
                    'group_id' => $group->id
                ],
                [
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'email' => 'jane.smith@test.com',
                    'user_type' => 'invitee',
                    'group_id' => null
                ]
            ]
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // Test invalid email
        $invalidEmailData = [
            'users' => [
                [
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'email' => 'invalid-email',
                    'user_type' => 'invitee'
                ]
            ]
        ];

        $validator = Validator::make($invalidEmailData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('users.0.email', $validator->errors()->toArray());

        // Test invalid user type
        $invalidUserTypeData = [
            'users' => [
                [
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'email' => 'test@test.com',
                    'user_type' => 'system_admin' // Not allowed in onboarding
                ]
            ]
        ];

        $validator = Validator::make($invalidUserTypeData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('users.0.user_type', $validator->errors()->toArray());

        // Test duplicate email
        User::factory()->create(['email' => 'existing@test.com']);
        
        $duplicateEmailData = [
            'users' => [
                [
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'email' => 'existing@test.com',
                    'user_type' => 'invitee'
                ]
            ]
        ];

        $validator = Validator::make($duplicateEmailData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('users.0.email', $validator->errors()->toArray());
    }

    /** @test */
    public function save_labels_request_validates_correctly()
    {
        $request = new SaveLabelsRequest();
        $rules = $request->rules();

        // Test valid data
        $validData = [
            'labels' => [
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
            ]
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // Test invalid color format
        $invalidColorData = [
            'labels' => [
                [
                    'name' => 'Test Label',
                    'color' => 'invalid-color',
                    'description' => 'Test description'
                ]
            ]
        ];

        $validator = Validator::make($invalidColorData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('labels.0.color', $validator->errors()->toArray());

        // Test missing required name
        $missingNameData = [
            'labels' => [
                [
                    'name' => '',
                    'color' => '#FF0000',
                    'description' => 'Test description'
                ]
            ]
        ];

        $validator = Validator::make($missingNameData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('labels.0.name', $validator->errors()->toArray());

        // Test too many labels
        $tooManyLabels = [
            'labels' => array_fill(0, 21, ['name' => 'Label', 'color' => '#000000'])
        ];

        $validator = Validator::make($tooManyLabels, $rules);
        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function all_requests_authorize_system_admin_only()
    {
        $requests = [
            new CreateBuildingsRequest(),
            new CreateRoomsRequest(),
            new CreateGroupsRequest(),
            new CreateUsersRequest(),
            new SaveLabelsRequest(),
        ];

        foreach ($requests as $request) {
            // Test system admin authorization
            $this->be($this->systemAdmin);
            $this->assertTrue($request->authorize(), get_class($request) . ' should authorize system admin');
            
            // Test regular user authorization
            $this->be($this->regularUser);
            $this->assertFalse($request->authorize(), get_class($request) . ' should not authorize regular user');
        }
    }

    /** @test */
    public function requests_handle_empty_arrays_correctly()
    {
        $requests = [
            new CreateBuildingsRequest() => ['buildings' => []],
            new CreateRoomsRequest() => ['rooms' => []],
            new CreateGroupsRequest() => ['groups' => []],
            new CreateUsersRequest() => ['users' => []],
            new SaveLabelsRequest() => ['labels' => []],
        ];

        foreach ($requests as $request => $data) {
            $rules = $request->rules();
            $validator = Validator::make($data, $rules);
            
            // Most requests should fail with empty arrays as they require at least 1 item
            $this->assertTrue($validator->fails(), get_class($request) . ' should fail with empty array');
        }
    }

    /** @test */
    public function requests_handle_malformed_data_gracefully()
    {
        $malformedData = [
            ['buildings' => 'not_an_array'],
            ['rooms' => [['name' => ['nested_array']]]],
            ['groups' => [null]],
            ['users' => [['email' => 123]]], // email as integer
            ['labels' => [['color' => ['not_a_string']]]],
        ];

        $requests = [
            new CreateBuildingsRequest(),
            new CreateRoomsRequest(),
            new CreateGroupsRequest(),
            new CreateUsersRequest(),
            new SaveLabelsRequest(),
        ];

        foreach ($malformedData as $index => $data) {
            $request = $requests[$index] ?? $requests[0];
            $rules = $request->rules();
            $validator = Validator::make($data, $rules);
            
            // Should fail gracefully without throwing exceptions
            $this->assertTrue($validator->fails(), 'Malformed data should be rejected');
        }
    }

    /** @test */
    public function requests_validate_string_lengths_correctly()
    {
        // Test very long strings
        $longString = str_repeat('a', 300);
        
        $buildingData = [
            'buildings' => [
                [
                    'name' => $longString,
                    'address' => $longString,
                    'description' => str_repeat('a', 1000)
                ]
            ]
        ];

        $request = new CreateBuildingsRequest();
        $validator = Validator::make($buildingData, $request->rules());
        
        // Should fail if strings are too long
        $this->assertTrue($validator->fails());
    }
}