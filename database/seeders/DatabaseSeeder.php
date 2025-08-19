<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Models\Building;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Get an instance of the PermissionRegistrar
        $permissionRegistrar = app(PermissionRegistrar::class);

        Schema::disableForeignKeyConstraints();
        Company::truncate();
        Role::truncate();
        // Clear the pivot table
        \DB::table(config('permission.table_names.model_has_roles'))->truncate();
        Schema::enableForeignKeyConstraints();

        // --- Create the first tenant: Innovate Corp ---
        $company = Company::factory()->create([
            'name' => 'Innovate Corp',
            'slug' => 'innovate',
        ]);

        // Make this company the current tenant
        $company->makeCurrent();

        // Tell Spatie which team to use
        $permissionRegistrar->setPermissionsTeamId($company->id);

        $adminRole = Role::create(['name' => 'Admin']); // team_id is now handled by the registrar

        User::factory(10)->create(['company_id' => $company->id])
            ->each(function ($user) use ($adminRole) {
                $user->assignRole($adminRole);
            });

        Building::factory(2)
            ->has(Room::factory()->count(5))
            ->create(['company_id' => $company->id]);

        // --- Create the second tenant: Quantum Solutions ---
        $secondCompany = Company::factory()->create([
            'name' => 'Quantum Solutions',
            'slug' => 'quantum',
        ]);

        $secondCompany->makeCurrent();

        // Tell Spatie to use the new team
        $permissionRegistrar->setPermissionsTeamId($secondCompany->id);

        $secondAdminRole = Role::create(['name' => 'Admin']);

        User::factory(5)->create(['company_id' => $secondCompany->id])
            ->each(function ($user) use ($secondAdminRole) {
                $user->assignRole($secondAdminRole);
            });

        Building::factory(1)
            ->has(Room::factory()->count(3))
            ->create(['company_id' => $secondCompany->id]);

        // Seed roles and permissions (this should run before users are created to avoid role conflicts)
        $this->call(RolePermissionSeeder::class);

        $this->call(ManualTestingSeeder::class);

        // Seeder complete - tenant context will be cleaned up automatically when process ends
    }
}
