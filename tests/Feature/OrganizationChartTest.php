<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationChartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_authenticated_user_can_view_organization_chart(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        $this->actingAs($ahmad)
            ->get('/organization-chart')
            ->assertStatus(200)
            ->assertSee('Bagan Struktur Organisasi')
            ->assertSee('Budi Santoso')
            ->assertSee('Ahmad Fauzi')
            ->assertSee('Information Technology');
    }

    public function test_guest_is_redirected_from_organization_chart(): void
    {
        $this->get('/organization-chart')
            ->assertRedirect('/login');
    }
}
