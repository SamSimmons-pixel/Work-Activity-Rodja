<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Models\Activity;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_user_can_view_activity_dashboard(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        $this->actingAs($ahmad)
            ->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('Aktivitas Kerja')
            ->assertSee('Total Aktivitas');
    }

    public function test_user_can_create_activity_with_audit_trail(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        $this->actingAs($ahmad);

        Livewire::test(DashboardIndex::class)
            ->call('openCreateModal')
            ->assertSet('isFormModalOpen', true)
            ->set('activity_date', '2026-08-18')
            ->set('activity', '<p>Migrated internal applications from Docker Compose to Nginx Proxy Manager.</p>')
            ->set('requested_by_option', 'Atasan Langsung')
            ->set('result', '<p>Seven internal services were successfully migrated.</p>')
            ->set('constraint', '<p>Two legacy applications require configuration adjustments.</p>')
            ->set('status', 'Submitted')
            ->call('saveActivity')
            ->assertSet('isFormModalOpen', false);

        $this->assertDatabaseHas('activities', [
            'user_id' => $ahmad->id,
            'requested_by' => 'Atasan Langsung',
            'status' => 'Submitted',
            'created_by' => $ahmad->id,
            'updated_by' => $ahmad->id,
        ]);
    }

    public function test_user_can_create_activity_with_custom_requested_by(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        $this->actingAs($ahmad);

        Livewire::test(DashboardIndex::class)
            ->call('openCreateModal')
            ->set('activity_date', '2026-08-20')
            ->set('activity', '<p>Setup email backup</p>')
            ->set('requested_by_option', 'Lainnya')
            ->set('requested_by_custom', 'Pak Hendra (Client)')
            ->set('result', '<p>Backup email aktif</p>')
            ->set('status', 'Submitted')
            ->call('saveActivity');

        $this->assertDatabaseHas('activities', [
            'user_id' => $ahmad->id,
            'requested_by' => 'Pak Hendra (Client)',
        ]);
    }

    public function test_user_can_edit_own_activity(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        $activity = Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => '2026-08-18',
            'activity' => '<p>Konfigurasi awal</p>',
            'requested_by' => 'Inisiatif Sendiri',
            'result' => '<p>Selesai sebagian</p>',
            'status' => 'Draft',
            'created_by' => $ahmad->id,
        ]);

        $this->actingAs($ahmad);

        Livewire::test(DashboardIndex::class)
            ->call('openEditModal', $activity->id)
            ->assertSet('isFormModalOpen', true)
            ->assertSet('editingActivityId', $activity->id)
            ->set('result', '<p>Selesai 100% dan terverifikasi</p>')
            ->set('status', 'Submitted')
            ->call('updateActivity')
            ->assertSet('isFormModalOpen', false);

        $activity->refresh();
        $this->assertStringContainsString('Selesai 100%', $activity->result);
        $this->assertEquals('Submitted', $activity->status);
        $this->assertEquals($ahmad->id, $activity->updated_by);
    }

    public function test_user_cannot_edit_other_users_activity_idor_prevention(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        $employeeRole = Role::where('name', 'Employee')->first();
        $siti = User::create([
            'username' => 'siti',
            'full_name' => 'Siti Nurhaliza',
            'password' => 'password123',
            'role_id' => $employeeRole->id,
            'status' => 'Active',
        ]);

        $sitiActivity = Activity::create([
            'user_id' => $siti->id,
            'activity_date' => '2026-08-18',
            'activity' => '<p>Rekap gaji</p>',
            'requested_by' => 'Finance Head',
            'result' => '<p>Selesai</p>',
            'status' => 'Submitted',
            'created_by' => $siti->id,
        ]);

        $this->actingAs($ahmad);

        Livewire::test(DashboardIndex::class)
            ->call('openEditModal', $sitiActivity->id)
            ->assertForbidden();
    }

    public function test_user_can_soft_delete_own_activity(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        $activity = Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => '2026-08-18',
            'activity' => '<p>Testing remove</p>',
            'requested_by' => 'Inisiatif Sendiri',
            'result' => '<p>Testing</p>',
            'status' => 'Submitted',
            'created_by' => $ahmad->id,
        ]);

        $this->actingAs($ahmad);

        Livewire::test(DashboardIndex::class)
            ->call('confirmDelete', $activity->id)
            ->assertSet('isDeleteModalOpen', true)
            ->assertSet('deletingActivityId', $activity->id)
            ->call('deleteActivity')
            ->assertSet('isDeleteModalOpen', false);

        $this->assertSoftDeleted('activities', [
            'id' => $activity->id,
            'deleted_by' => $ahmad->id,
        ]);
    }

    public function test_supervisor_can_view_subordinate_activities(): void
    {
        $budi = User::where('username', 'budi')->first(); // Supervisor
        $ahmad = User::where('username', 'ahmad')->first(); // Subordinate

        Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => now()->toDateString(),
            'activity' => '<p>Pekerjaan Ahmad IT Support</p>',
            'requested_by' => 'IT Manager',
            'result' => '<p>Tuntas</p>',
            'status' => 'Submitted',
            'created_by' => $ahmad->id,
        ]);

        $this->actingAs($budi);

        Livewire::test(DashboardIndex::class)
            ->set('selectedUserId', (string) $ahmad->id)
            ->assertSee('Pekerjaan Ahmad IT Support')
            ->assertSee($ahmad->full_name);
    }

    public function test_search_filters_activities_properly(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();
        $today = now()->toDateString();

        Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => $today,
            'activity' => '<p>Migrasi konfigurasi Nginx Proxy</p>',
            'requested_by' => 'IT Manager',
            'result' => '<p>Berhasil</p>',
            'status' => 'Submitted',
            'created_by' => $ahmad->id,
        ]);

        Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => $today,
            'activity' => '<p>Perbaikan kabel LAN switch</p>',
            'requested_by' => 'Inisiatif Sendiri',
            'result' => '<p>Kabel terpasang</p>',
            'status' => 'Submitted',
            'created_by' => $ahmad->id,
        ]);

        $this->actingAs($ahmad);

        Livewire::test(DashboardIndex::class)
            ->set('search', 'Nginx')
            ->assertSee('Migrasi konfigurasi Nginx Proxy')
            ->assertDontSee('Perbaikan kabel LAN switch');
    }
}
