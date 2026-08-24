<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Models\Activity;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryAndAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_user_can_create_activity_with_category_and_tags(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        $this->actingAs($ahmad);

        Livewire::test(DashboardIndex::class)
            ->call('openCreateModal')
            ->set('activity_date', now()->toDateString())
            ->set('category', 'Infrastructure')
            ->set('tags_input', 'Server, Ubuntu, Nginx')
            ->set('activity', '<p>Setup VM Server</p>')
            ->set('requested_by_option', 'Inisiatif Sendiri')
            ->set('result', '<p>Server siap digunakan</p>')
            ->set('status', 'Submitted')
            ->call('saveActivity')
            ->assertSet('isFormModalOpen', false);

        $activity = Activity::where('user_id', $ahmad->id)->first();
        $this->assertEquals('Infrastructure', $activity->category);
        $this->assertEquals(['Server', 'Ubuntu', 'Nginx'], $activity->tags);
    }

    public function test_dashboard_can_switch_to_analytics_view(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        $this->actingAs($ahmad);

        Livewire::test(DashboardIndex::class)
            ->assertSet('viewMode', 'timeline')
            ->call('setViewMode', 'analytics')
            ->assertSet('viewMode', 'analytics')
            ->assertSee('Distribusi Kategori Pekerjaan')
            ->assertSee('Tren Frekuensi Aktivitas Harian');
    }

    public function test_category_filter_filters_activities(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => now()->toDateString(),
            'category' => 'Security',
            'activity' => '<p>Firewall hardening</p>',
            'requested_by' => 'Direktur',
            'result' => '<p>Firewall aktif</p>',
            'status' => 'Submitted',
            'created_by' => $ahmad->id,
            'updated_by' => $ahmad->id,
        ]);

        Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => now()->toDateString(),
            'category' => 'Development',
            'activity' => '<p>Coding fitur baru</p>',
            'requested_by' => 'Inisiatif Sendiri',
            'result' => '<p>Fitur selesai</p>',
            'status' => 'Submitted',
            'created_by' => $ahmad->id,
            'updated_by' => $ahmad->id,
        ]);

        $this->actingAs($ahmad);

        Livewire::test(DashboardIndex::class)
            ->set('selectedCategory', 'Security')
            ->assertSee('Firewall hardening')
            ->assertDontSee('Coding fitur baru');
    }
}
