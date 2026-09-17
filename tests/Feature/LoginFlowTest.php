<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * End-to-end coverage of the authentication flow:
 * login page, credentials check, redirect to dashboard, auth guard,
 * and logout back to the login page.
 */
class LoginFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_the_login_page(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')->assertRedirectToRoute('login');
    }

    public function test_valid_credentials_login_and_redirect_to_dashboard(): void
    {
        $this->seedAdmin();

        $this->post('/login', [
            'login' => 'admin@vendorhijabbandung.com',
            'password' => 'password123',
        ])->assertRedirect('/dashboard');
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->seedAdmin();

        $this->from('/login')->post('/login', [
            'login' => 'admin@vendorhijabbandung.com',
            'password' => 'incorrect123',
        ])->assertRedirectBackWithErrors(['login']);
    }

    public function test_authenticated_user_can_access_dashboard_after_login(): void
    {
        $this->seedAdmin();

        $this->post('/login', [
            'login' => 'admin@vendorhijabbandung.com',
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertStatus(200);
    }

    public function test_logout_returns_to_login_and_revokes_access(): void
    {
        $this->seedAdmin();

        // Login first.
        $this->post('/login', [
            'login' => 'admin@vendorhijabbandung.com',
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        // Logout redirects back to the login page.
        $this->post('/logout')->assertRedirectToRoute('login');

        // The dashboard is protected again after logout.
        $this->get('/dashboard')->assertRedirectToRoute('login');
    }

    /**
     * Create the seeded-style admin account used by the login test.
     */
    private function seedAdmin(): User
    {
        return User::create([
            'name' => 'Admin Vendor Hijab Bandung',
            'email' => 'admin@vendorhijabbandung.com',
            'password' => Hash::make('password123'),
        ]);
    }
}