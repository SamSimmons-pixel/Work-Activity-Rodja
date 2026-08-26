<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Notifications\NotificationMenu;
use App\Livewire\Reviews\Index as ReviewsIndex;
use App\Models\Activity;
use App\Models\User;
use App\Notifications\ActivityCommentedNotification;
use App\Notifications\ActivityVerifiedNotification;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_authenticated_user_can_view_notification_bell_menu(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        $this->actingAs($ahmad);

        Livewire::test(NotificationMenu::class)
            ->assertSeeHtml('title="Notifikasi"')
            ->assertSet('isOpen', false)
            ->call('toggleMenu')
            ->assertSet('isOpen', true)
            ->assertSee('Notifikasi')
            ->assertSee('Belum ada notifikasi');
    }

    public function test_notification_is_created_when_activity_is_commented(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();
        $budi = User::where('username', 'budi')->first();

        $activity = Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => now()->toDateString(),
            'category' => 'Development',
            'activity' => '<p>Membangun API login</p>',
            'requested_by' => 'Inisiatif Sendiri',
            'result' => '<p>Endpoint siap</p>',
            'status' => 'Submitted',
            'created_by' => $ahmad->id,
            'updated_by' => $ahmad->id,
        ]);

        // Supervisor Budi adds comment on Ahmad's activity
        $this->actingAs($budi);

        Livewire::test(DashboardIndex::class)
            ->set('commentingActivityId', $activity->id)
            ->set('newCommentText', 'Kerja bagus, lanjutkan ke unit test.')
            ->call('addComment', $activity->id);

        // Ahmad should receive 1 unread notification
        $this->assertEquals(1, $ahmad->unreadNotifications()->count());

        $notification = $ahmad->unreadNotifications()->first();
        $this->assertEquals('comment', $notification->data['type']);
        $this->assertEquals('Budi Santoso', $notification->data['sender_name']);

        // Ahmad views notification dropdown with State 1 (highlighted / unread)
        $this->actingAs($ahmad);

        Livewire::test(NotificationMenu::class)
            ->assertSee('Budi Santoso memberikan catatan')
            ->assertSee('Belum Dibaca (1)')
            ->assertSeeHtml('bg-rose-600') // Red circle badge on bell
            ->assertSeeHtml('bg-indigo-600'); // Unread indicator dot
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();
        $budi = User::where('username', 'budi')->first();

        $activity = Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => now()->toDateString(),
            'category' => 'Infrastructure',
            'activity' => '<p>Backup Database Server</p>',
            'requested_by' => 'IT Manager',
            'result' => '<p>Backup selesai</p>',
            'status' => 'Submitted',
            'created_by' => $ahmad->id,
            'updated_by' => $ahmad->id,
        ]);

        $ahmad->notify(new ActivityVerifiedNotification($activity, $budi, 'Verified'));

        $this->assertEquals(1, $ahmad->unreadNotifications()->count());
        $notification = $ahmad->unreadNotifications()->first();

        $this->actingAs($ahmad);

        // Mark single notification as read
        Livewire::test(NotificationMenu::class)
            ->call('markAsRead', $notification->id)
            ->assertSee('Belum Dibaca (0)')
            ->set('filter', 'unread')
            ->assertSee('Semua notifikasi telah Anda baca.');

        $this->assertEquals(0, $ahmad->unreadNotifications()->count());
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();
        $budi = User::where('username', 'budi')->first();

        $activity = Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => now()->toDateString(),
            'category' => 'Development',
            'activity' => '<p>Task 1</p>',
            'requested_by' => 'Manager',
            'result' => '<p>Done</p>',
            'status' => 'Submitted',
            'created_by' => $ahmad->id,
            'updated_by' => $ahmad->id,
        ]);

        $ahmad->notify(new ActivityCommentedNotification($activity, $budi, 'Comment 1'));
        $ahmad->notify(new ActivityVerifiedNotification($activity, $budi, 'Verified'));

        $this->assertEquals(2, $ahmad->unreadNotifications()->count());

        $this->actingAs($ahmad);

        Livewire::test(NotificationMenu::class)
            ->call('markAllAsRead')
            ->assertSee('Belum Dibaca (0)');

        $this->assertEquals(0, $ahmad->fresh()->unreadNotifications()->count());
    }

    public function test_user_can_delete_notification(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();
        $budi = User::where('username', 'budi')->first();

        $activity = Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => now()->toDateString(),
            'category' => 'Development',
            'activity' => '<p>Task Test</p>',
            'requested_by' => 'Manager',
            'result' => '<p>Done</p>',
            'status' => 'Submitted',
            'created_by' => $ahmad->id,
            'updated_by' => $ahmad->id,
        ]);

        $ahmad->notify(new ActivityCommentedNotification($activity, $budi, 'Test comment'));
        $notification = $ahmad->notifications()->first();

        $this->actingAs($ahmad);

        Livewire::test(NotificationMenu::class)
            ->call('deleteNotification', $notification->id);

        $this->assertEquals(0, $ahmad->fresh()->notifications()->count());
    }

    public function test_subordinate_receives_notification_on_performance_review(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();
        $budi = User::where('username', 'budi')->first();

        $this->actingAs($budi);

        Livewire::test(ReviewsIndex::class)
            ->call('openCreateModal')
            ->set('user_id', $ahmad->id)
            ->set('period_type', 'Quarterly')
            ->set('period_label', 'Kuartal 3 2026')
            ->set('start_date', '2026-07-01')
            ->set('end_date', '2026-09-30')
            ->set('rating', 'Sangat Baik (A)')
            ->set('summary', 'Pencapaian penyelesaian tiket IT sangat memuaskan.')
            ->set('status', 'Final')
            ->call('saveReview');

        $this->assertEquals(1, $ahmad->unreadNotifications()->count());
        $notification = $ahmad->unreadNotifications()->first();
        $this->assertEquals('review', $notification->data['type']);
        $this->assertStringContainsString('Kuartal 3 2026', $notification->data['message']);
    }

    public function test_user_cannot_see_other_users_notifications(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();
        $budi = User::where('username', 'budi')->first();

        $activity = Activity::create([
            'user_id' => $budi->id,
            'activity_date' => now()->toDateString(),
            'category' => 'Development',
            'activity' => '<p>Private manager task</p>',
            'requested_by' => 'Direktur',
            'result' => '<p>Done</p>',
            'status' => 'Submitted',
            'created_by' => $budi->id,
            'updated_by' => $budi->id,
        ]);

        $budi->notify(new ActivityCommentedNotification($activity, $budi, 'Private note'));

        // Ahmad logs in and views notifications
        $this->actingAs($ahmad);

        Livewire::test(NotificationMenu::class)
            ->assertDontSee('Private manager task')
            ->assertDontSee('Private note');
    }
}
