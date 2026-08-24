<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Work Activity');
        $response->assertSee('Username');
        $response->assertSee('Kata Sandi');
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_user_can_authenticate_with_valid_credentials(): void
    {
        Livewire::test(Login::class)
            ->set('username', 'admin')
            ->set('password', 'admin123')
            ->call('authenticate')
            ->assertRedirect(route('dashboard'));

        $this->assertTrue(Auth::check());
        $this->assertEquals('admin', Auth::user()->username);
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        Livewire::test(Login::class)
            ->set('username', 'admin')
            ->set('password', 'wrong-password')
            ->call('authenticate')
            ->assertHasErrors(['username']);

        $this->assertFalse(Auth::check());
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        $inactiveUser = User::where('username', 'ahmad')->first();
        $inactiveUser->update(['status' => 'Inactive']);

        Livewire::test(Login::class)
            ->set('username', 'ahmad')
            ->set('password', 'password123')
            ->call('authenticate')
            ->assertHasErrors(['username']);

        $this->assertFalse(Auth::check());
    }

    public function test_user_can_logout(): void
    {
        $user = User::where('username', 'admin')->first();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertFalse(Auth::check());
    }
}
