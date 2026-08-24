<?php

namespace Tests\Feature;

use App\Livewire\Admin\Organization\Index as OrganizationIndex;
use App\Models\Division;
use App\Models\Position;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrganizationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_view_organization_page(): void
    {
        $admin = User::where('username', 'admin')->first();

        $this->actingAs($admin)
            ->get('/admin/organization')
            ->assertStatus(200)
            ->assertSee('Struktur Organisasi')
            ->assertSee('Divisi Kerja');
    }

    public function test_non_admin_cannot_access_organization_management(): void
    {
        $employee = User::where('username', 'ahmad')->first();

        $this->actingAs($employee)
            ->get('/admin/organization')
            ->assertForbidden();
    }

    public function test_admin_can_create_and_update_division(): void
    {
        $admin = User::where('username', 'admin')->first();

        $this->actingAs($admin);

        // Create Division
        Livewire::test(OrganizationIndex::class)
            ->call('openCreateDivisionModal')
            ->set('division_name', 'Creative Media')
            ->set('division_status', 'Active')
            ->call('saveDivision')
            ->assertSet('isDivisionModalOpen', false);

        $this->assertDatabaseHas('divisions', [
            'name' => 'Creative Media',
            'status' => 'Active',
        ]);

        $division = Division::where('name', 'Creative Media')->first();

        // Update Division
        Livewire::test(OrganizationIndex::class)
            ->call('openEditDivisionModal', $division->id)
            ->set('division_name', 'Creative & Multimedia')
            ->call('saveDivision')
            ->assertSet('isDivisionModalOpen', false);

        $division->refresh();
        $this->assertEquals('Creative & Multimedia', $division->name);
    }

    public function test_admin_can_create_and_update_position(): void
    {
        $admin = User::where('username', 'admin')->first();
        $itDivision = Division::where('name', 'Information Technology')->first();

        $this->actingAs($admin);

        // Create Position
        Livewire::test(OrganizationIndex::class)
            ->call('openCreatePositionModal')
            ->set('position_name', 'Cyber Security Specialist')
            ->set('position_division_id', $itDivision->id)
            ->set('position_level', '2')
            ->set('position_status', 'Active')
            ->call('savePosition')
            ->assertSet('isPositionModalOpen', false);

        $this->assertDatabaseHas('positions', [
            'name' => 'Cyber Security Specialist',
            'division_id' => $itDivision->id,
            'level' => '2',
            'status' => 'Active',
        ]);

        $pos = Position::where('name', 'Cyber Security Specialist')->first();

        // Update Position
        Livewire::test(OrganizationIndex::class)
            ->call('openEditPositionModal', $pos->id)
            ->set('position_level', '1')
            ->call('savePosition')
            ->assertSet('isPositionModalOpen', false);

        $pos->refresh();
        $this->assertEquals('1', $pos->level);
    }
}
