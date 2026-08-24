<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allPermissions = Permission::all();

        // 1. Administrator - All permissions
        $adminRole = Role::where('name', 'Administrator')->first();
        if ($adminRole) {
            $adminRole->permissions()->sync($allPermissions->pluck('id'));
        }

        // 2. Supervisor
        $supervisorRole = Role::where('name', 'Supervisor')->first();
        if ($supervisorRole) {
            $supervisorPerms = Permission::whereIn('name', [
                'activity.create',
                'activity.read.own',
                'activity.update.own',
                'activity.delete.own',
                'activity.read.subordinate',
            ])->pluck('id');
            $supervisorRole->permissions()->sync($supervisorPerms);
        }

        // 3. Employee
        $employeeRole = Role::where('name', 'Employee')->first();
        if ($employeeRole) {
            $employeePerms = Permission::whereIn('name', [
                'activity.create',
                'activity.read.own',
                'activity.update.own',
                'activity.delete.own',
            ])->pluck('id');
            $employeeRole->permissions()->sync($employeePerms);
        }

        // 4. Management
        $mgmtRole = Role::where('name', 'Management')->first();
        if ($mgmtRole) {
            $mgmtPerms = Permission::whereIn('name', [
                'activity.read.own',
                'activity.read.subordinate',
                'activity.read.division',
                'activity.read.all',
            ])->pluck('id');
            $mgmtRole->permissions()->sync($mgmtPerms);
        }
    }
}
