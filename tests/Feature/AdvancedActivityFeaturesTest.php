<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Reports\MonthlyReport;
use App\Models\Activity;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdvancedActivityFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('public');
    }

    public function test_user_can_create_activity_with_attachment(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();
        $file = UploadedFile::fake()->create('network_config.pdf', 500, 'application/pdf');

        $this->actingAs($ahmad);

        Livewire::test(DashboardIndex::class)
            ->call('openCreateModal')
            ->set('activity_date', now()->toDateString())
            ->set('activity', '<p>Konfigurasi Router Baru</p>')
            ->set('requested_by_option', 'Inisiatif Sendiri')
            ->set('result', '<p>Router berhasil online</p>')
            ->set('status', 'Submitted')
            ->set('attachment', $file)
            ->call('saveActivity')
            ->assertSet('isFormModalOpen', false);

        $activity = Activity::where('user_id', $ahmad->id)->first();
        $this->assertNotNull($activity->attachment_path);
        $this->assertEquals('network_config.pdf', $activity->attachment_name);
        Storage::disk('public')->assertExists($activity->attachment_path);
    }

    public function test_supervisor_can_add_comment_to_subordinate_activity(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();
        $budi = User::where('username', 'budi')->first(); // Supervisor

        $activity = Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => now()->toDateString(),
            'activity' => '<p>Instalasi Server Database</p>',
            'requested_by' => 'Atasan Langsung',
            'result' => '<p>Database terinstal</p>',
            'status' => 'Submitted',
            'created_by' => $ahmad->id,
            'updated_by' => $ahmad->id,
        ]);

        $this->actingAs($budi);

        Livewire::test(DashboardIndex::class)
            ->set('newCommentText', 'Pekerjaan sangat baik, pastikan backup otomatis aktif.')
            ->call('addComment', $activity->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('activity_comments', [
            'activity_id' => $activity->id,
            'user_id' => $budi->id,
            'comment' => 'Pekerjaan sangat baik, pastikan backup otomatis aktif.',
        ]);
    }

    public function test_supervisor_can_verify_subordinate_activity(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();
        $budi = User::where('username', 'budi')->first();

        $activity = Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => now()->toDateString(),
            'activity' => '<p>Audit Keamanan Jaringan</p>',
            'requested_by' => 'Direktur',
            'result' => '<p>Laporan audit selesai</p>',
            'status' => 'Submitted',
            'created_by' => $ahmad->id,
            'updated_by' => $ahmad->id,
        ]);

        $this->actingAs($budi);

        Livewire::test(DashboardIndex::class)
            ->call('verifyActivity', $activity->id, 'Verified')
            ->assertHasNoErrors();

        $activity->refresh();
        $this->assertEquals('Verified', $activity->status);
        $this->assertEquals($budi->id, $activity->verified_by);
        $this->assertNotNull($activity->verified_at);
    }

    public function test_employee_cannot_verify_activities(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        $activity = Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => now()->toDateString(),
            'activity' => '<p>Tugas Mandiri</p>',
            'requested_by' => 'Inisiatif Sendiri',
            'result' => '<p>Selesai</p>',
            'status' => 'Submitted',
            'created_by' => $ahmad->id,
            'updated_by' => $ahmad->id,
        ]);

        $this->actingAs($ahmad);

        Livewire::test(DashboardIndex::class)
            ->call('verifyActivity', $activity->id, 'Verified')
            ->assertForbidden();
    }

    public function test_user_can_view_monthly_report(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => now()->toDateString(),
            'activity' => '<p>Pekerjaan Laporan</p>',
            'requested_by' => 'Inisiatif Sendiri',
            'result' => '<p>Hasil Laporan</p>',
            'status' => 'Submitted',
            'created_by' => $ahmad->id,
            'updated_by' => $ahmad->id,
        ]);

        $this->actingAs($ahmad)
            ->get(route('reports.monthly', ['user_id' => $ahmad->id, 'year' => now()->year, 'month' => now()->month]))
            ->assertStatus(200)
            ->assertSee('Work Activity Report', false)
            ->assertSee('Ahmad Fauzi')
            ->assertSee('Pekerjaan Laporan', false);
    }

    public function test_supervisor_can_view_subordinate_monthly_report(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();
        $budi = User::where('username', 'budi')->first();

        $this->actingAs($budi)
            ->get(route('reports.monthly', ['user_id' => $ahmad->id, 'year' => now()->year, 'month' => now()->month]))
            ->assertStatus(200)
            ->assertSee('Ahmad Fauzi');
    }
}
