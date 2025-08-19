<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Company;
use App\Models\Group;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ManualTestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // Clean up previous data
        User::where('email', 'like', '%@venuepro.com')->delete();
        Group::where('name', 'Test Group')->delete();
        Room::where('name', 'Test Room 101')->delete();
        Building::where('name', 'Main Building')->delete();
        Company::where('name', 'Test Company')->delete();


        Schema::enableForeignKeyConstraints();

        // Create a company
        $company = Company::factory()->create([
            'name' => 'Test Company',
        ]);

        // Create a building
        $building = Building::factory()->create([
            'company_id' => $company->id,
            'name' => 'Main Building',
        ]);

        // Create a room
        $room = Room::factory()->create([
            'company_id' => $company->id,
            'building_id' => $building->id,
            'name' => 'Test Room 101',
        ]);

        // Create a group
        $group = Group::factory()->create([
            'company_id' => $company->id,
            'name' => 'Test Group',
        ]);

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'system_admin']);
        $baRole = Role::firstOrCreate(['name' => 'business_analyst']);
        $hodRole = Role::firstOrCreate(['name' => 'hod']);

        // Create a system admin user
        $admin = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => 'admin@venuepro.com',
            'company_id' => $company->id,
        ]);
        $admin->assignRole($adminRole);

        // Create a BA user
        $ba = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'BA',
            'email' => 'ba@venuepro.com',
            'company_id' => $company->id,
        ]);
        $ba->assignRole($baRole);

        // Create an HOD user
        $hod = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'HOD',
            'email' => 'hod@venuepro.com',
            'company_id' => $company->id,
        ]);
        $hod->assignRole($hodRole);

        // You can create labels here if you have a Label model and factory
        // \App\Models\Label::factory(5)->create(['company_id' => $company->id]);
    }
}