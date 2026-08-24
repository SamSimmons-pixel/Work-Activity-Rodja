<?php

namespace Tests\Feature;

use App\Livewire\Admin\Users\Index as UsersIndex;
use App\Models\Division;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_view_user_management_page(): void
    {
        $admin = User::where('username', 'admin')->first();

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertStatus(200)
            ->assertSee('Manajemen Pengguna')
            ->assertSee('Tambah Pengguna');
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $employee = User::where('username', 'ahmad')->first();

        $this->actingAs($employee)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_admin_can_create_new_user_with_full_hierarchy(): void
    {
        $admin = User::where('username', 'admin')->first();
        $budi = User::where('username', 'budi')->first(); // Supervisor
        $employeeRole = Role::where('name', 'Employee')->first();
        $itDivision = Division::where('name', 'Information Technology')->first();
        $developerPos = Position::where('name', 'Developer')->first();

        $this->actingAs($admin);

        Livewire::test(UsersIndex::class)
            ->call('openCreateModal')
            ->set('full_name', 'Dewi Lestari')
            ->set('username', 'dewi')
            ->set('password', 'secret123')
            ->set('password_confirmation', 'secret123')
            ->set('division_id', $itDivision->id)
            ->set('position_id', $developerPos->id)
            ->set('supervisor_id', $budi->id)
            ->set('role_id', $employeeRole->id)
            ->set('status', 'Active')
            ->call('createUser')
            ->assertSet('isFormModalOpen', false);

        $this->assertDatabaseHas('users', [
            'username' => 'dewi',
            'full_name' => 'Dewi Lestari',
            'division_id' => $itDivision->id,
            'position_id' => $developerPos->id,
            'supervisor_id' => $budi->id,
            'role_id' => $employeeRole->id,
            'status' => 'Active',
        ]);

        $createdUser = User::where('username', 'dewi')->first();
        $this->assertTrue(Hash::check('secret123', $createdUser->password));
    }

    public function test_admin_can_edit_existing_user(): void
    {
        $admin = User::where('username', 'admin')->first();
        $ahmad = User::where('username', 'ahmad')->first();
        $sysAdminPos = Position::where('name', 'System Administrator')->first();

        $this->actingAs($admin);

        Livewire::test(UsersIndex::class)
            ->call('openEditModal', $ahmad->id)
            ->assertSet('isFormModalOpen', true)
            ->set('position_id', $sysAdminPos->id)
            ->set('full_name', 'Ahmad Fauzi, S.Kom')
            ->call('updateUser')
            ->assertSet('isFormModalOpen', false);

        $ahmad->refresh();
        $this->assertEquals('Ahmad Fauzi, S.Kom', $ahmad->full_name);
        $this->assertEquals($sysAdminPos->id, $ahmad->position_id);
    }

    public function test_admin_can_reset_user_password(): void
    {
        $admin = User::where('username', 'admin')->first();
        $ahmad = User::where('username', 'ahmad')->first();

        $this->actingAs($admin);

        Livewire::test(UsersIndex::class)
            ->call('openResetPasswordModal', $ahmad->id)
            ->assertSet('isResetPasswordModalOpen', true)
            ->set('new_password', 'newsecretpass')
            ->set('new_password_confirmation', 'newsecretpass')
            ->call('resetPassword')
            ->assertSet('isResetPasswordModalOpen', false);

        $ahmad->refresh();
        $this->assertTrue(Hash::check('newsecretpass', $ahmad->password));
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $admin = User::where('username', 'admin')->first();
        $ahmad = User::where('username', 'ahmad')->first();

        $this->actingAs($admin);

        // Deactivate Ahmad
        Livewire::test(UsersIndex::class)
            ->call('toggleStatus', $ahmad->id);

        $ahmad->refresh();
        $this->assertEquals('Inactive', $ahmad->status);

        // Reactivate Ahmad
        Livewire::test(UsersIndex::class)
            ->call('toggleStatus', $ahmad->id);

        $ahmad->refresh();
        $this->assertEquals('Active', $ahmad->status);
    }
}
