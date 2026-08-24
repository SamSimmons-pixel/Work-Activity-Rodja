<?php

namespace Tests\Feature;

use App\Livewire\Profile\Index as ProfileIndex;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        $this->actingAs($ahmad)
            ->get('/profile')
            ->assertStatus(200)
            ->assertSee('Profil Pengguna')
            ->assertSee('Ahmad Fauzi')
            ->assertSee('Information Technology')
            ->assertSee('IT Support')
            ->assertSee('Budi Santoso');
    }

    public function test_unauthenticated_user_cannot_view_profile_page(): void
    {
        $this->get('/profile')
            ->assertRedirect('/login');
    }

    public function test_user_can_update_profile_name(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        $this->actingAs($ahmad);

        Livewire::test(ProfileIndex::class)
            ->set('full_name', 'Ahmad Fauzi Perkasa')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $ahmad->refresh();
        $this->assertEquals('Ahmad Fauzi Perkasa', $ahmad->full_name);
    }

    public function test_user_can_change_password_successfully(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        $this->actingAs($ahmad);

        Livewire::test(ProfileIndex::class)
            ->set('current_password', 'password123')
            ->set('password', 'newSecurePass123')
            ->set('password_confirmation', 'newSecurePass123')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $ahmad->refresh();
        $this->assertTrue(Hash::check('newSecurePass123', $ahmad->password));
    }

    public function test_user_cannot_change_password_with_incorrect_current_password(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        $this->actingAs($ahmad);

        Livewire::test(ProfileIndex::class)
            ->set('current_password', 'wrongCurrentPassword')
            ->set('password', 'newSecurePass123')
            ->set('password_confirmation', 'newSecurePass123')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);

        $ahmad->refresh();
        $this->assertTrue(Hash::check('password123', $ahmad->password));
    }

    public function test_admin_can_update_own_username_in_profile(): void
    {
        $admin = User::where('username', 'admin')->first();

        $this->actingAs($admin);

        Livewire::test(ProfileIndex::class)
            ->set('full_name', 'Super Admin Utama')
            ->set('username', 'superadmin')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $admin->refresh();
        $this->assertEquals('superadmin', $admin->username);
        $this->assertEquals('Super Admin Utama', $admin->full_name);
    }

    public function test_non_admin_cannot_update_own_username_in_profile(): void
    {
        $ahmad = User::where('username', 'ahmad')->first();

        $this->actingAs($ahmad);

        Livewire::test(ProfileIndex::class)
            ->set('full_name', 'Ahmad Fauzi')
            ->set('username', 'ahmad_hacker')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $ahmad->refresh();
        // Username must remain original 'ahmad'
        $this->assertEquals('ahmad', $ahmad->username);
    }
}
