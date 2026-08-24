<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExcelExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_user_can_export_own_activities_to_excel(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => now()->toDateString(),
            'category' => 'Infrastructure',
            'tags' => ['Network', 'Router'],
            'activity' => '<p>Perbaikan Kabel LAN</p>',
            'requested_by' => 'Divisi HRD',
            'result' => '<p>Koneksi internet lancar kembali</p>',
            'status' => 'Submitted',
            'created_by' => $ahmad->id,
            'updated_by' => $ahmad->id,
        ]);

        $response = $this->actingAs($ahmad)
            ->get(route('activities.export-excel', [
                'user_id' => $ahmad->id,
                'year' => now()->year,
                'month' => now()->month,
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition'), '.xls'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('REKAPITULASI AKTIVITAS KERJA KARYAWAN', $content);
        $this->assertStringContainsString('Perbaikan Kabel LAN', $content);
        $this->assertStringContainsString('Infrastructure', $content);
        $this->assertStringContainsString('Network, Router', $content);
        $this->assertStringContainsString('Deskripsi Aktivitas', $content);
        $this->assertStringContainsString('Hasil / Luaran', $content);
    }

    public function test_supervisor_can_export_subordinates_activities(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();
        $budi = User::where('username', 'budi')->first();

        $response = $this->actingAs($budi)
            ->get(route('activities.export-excel', [
                'user_id' => $ahmad->id,
                'year' => now()->year,
                'month' => now()->month,
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
    }

    public function test_non_supervisor_cannot_export_others_activities(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();
        $budi = User::where('username', 'budi')->first();

        // Ahmad tries to export Budi's (his supervisor's) data
        $response = $this->actingAs($ahmad)
            ->get(route('activities.export-excel', [
                'user_id' => $budi->id,
                'year' => now()->year,
                'month' => now()->month,
            ]));

        $response->assertForbidden();
    }
}
