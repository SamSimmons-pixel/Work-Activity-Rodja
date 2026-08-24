<?php

namespace Tests\Feature;

use App\Livewire\Admin\Roles\Index as RolesIndex;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_view_roles_page(): void
    {
        $admin = User::where('username', 'admin')->first();

        $this->actingAs($admin)
            ->get('/admin/roles')
            ->assertStatus(200)
            ->assertSee('Manajemen Peran')
            ->assertSee('Supervisor')
            ->assertSee('Employee');
    }

    public function test_non_admin_cannot_access_roles_page(): void
    {
        $employee = User::where('username', 'ahmad')->first();

        $this->actingAs($employee)
            ->get('/admin/roles')
            ->assertForbidden();
    }

    public function test_admin_can_update_role_permissions(): void
    {
        $admin = User::where('username', 'admin')->first();
        $employeeRole = Role::where('name', 'Employee')->first();
        $createActivityPerm = Permission::where('name', 'activity.create')->first();

        $this->actingAs($admin);

        Livewire::test(RolesIndex::class)
            ->call('openEditModal', $employeeRole->id)
            ->assertSet('isEditModalOpen', true)
            ->set('role_description', 'Updated Employee Description')
            ->set('selectedPermissions', [$createActivityPerm->id])
            ->call('updateRolePermissions')
            ->assertSet('isEditModalOpen', false);

        $employeeRole->refresh();
        $this->assertEquals('Updated Employee Description', $employeeRole->description);
        $this->assertCount(1, $employeeRole->permissions);
        $this->assertEquals($createActivityPerm->id, $employeeRole->permissions->first()->id);
    }
}
