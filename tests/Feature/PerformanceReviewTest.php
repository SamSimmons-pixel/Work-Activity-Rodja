<?php

namespace Tests\Feature;

use App\Livewire\Reviews\Index as ReviewsIndex;
use App\Models\PerformanceReview;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PerformanceReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_supervisor_can_view_reviews_page(): void
    {
        $budi = User::where('username', 'budi')->first();

        $this->actingAs($budi)
            ->get('/performance-reviews')
            ->assertStatus(200)
            ->assertSee('Review Kinerja Berkala')
            ->assertSee('Buat Review Kinerja');
    }

    public function test_supervisor_can_create_performance_review_for_subordinate(): void
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
            ->set('strengths', 'Disiplin dan teliti.')
            ->set('improvements', 'Eksplorasi automasi script.')
            ->set('status', 'Final')
            ->call('saveReview')
            ->assertSet('isFormModalOpen', false);

        $this->assertDatabaseHas('performance_reviews', [
            'user_id' => $ahmad->id,
            'reviewer_id' => $budi->id,
            'period_label' => 'Kuartal 3 2026',
            'rating' => 'Sangat Baik (A)',
            'summary' => 'Pencapaian penyelesaian tiket IT sangat memuaskan.',
        ]);
    }

    public function test_employee_can_view_received_reviews(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();
        $budi = User::where('username', 'budi')->first();

        PerformanceReview::create([
            'user_id' => $ahmad->id,
            'reviewer_id' => $budi->id,
            'period_type' => 'Quarterly',
            'period_label' => 'Kuartal 2 2026',
            'start_date' => '2026-04-01',
            'end_date' => '2026-06-30',
            'rating' => 'Baik (B)',
            'summary' => 'Performa kerja stabil dan tepat waktu.',
            'status' => 'Final',
        ]);

        $this->actingAs($ahmad)
            ->get('/performance-reviews')
            ->assertStatus(200)
            ->assertSee('Kuartal 2 2026')
            ->assertSee('Baik (B)')
            ->assertSee('Performa kerja stabil dan tepat waktu.');
    }

    public function test_employee_cannot_create_review_for_supervisor(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();
        $budi = User::where('username', 'budi')->first();

        $this->actingAs($ahmad);

        // Ahmad tries to review Budi (his supervisor)
        Livewire::test(ReviewsIndex::class)
            ->set('user_id', $budi->id)
            ->set('period_type', 'Quarterly')
            ->set('period_label', 'Kuartal 3 2026')
            ->set('start_date', '2026-07-01')
            ->set('end_date', '2026-09-30')
            ->set('rating', 'Sangat Baik (A)')
            ->set('summary', 'Pimpinan yang baik.')
            ->call('saveReview')
            ->assertForbidden();
    }
}
