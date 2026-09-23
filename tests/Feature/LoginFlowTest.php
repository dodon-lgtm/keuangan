<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_seeded_admin_can_login_with_the_seeder_credentials(): void
    {
        $this->seed(UserSeeder::class);

        $this->post('/login', [
            'login' => 'admin@vendorhijabbandung.com',
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_login_with_email_is_case_insensitive(): void
    {
        $this->seedAdmin();

        $this->post('/login', [
            'login' => 'ADMIN@VendorHijabBandung.com',
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_seeded_admin_can_login_with_username(): void
    {
        $this->seedAdmin();

        $this->post('/login', [
            'login' => 'Admin Vendor Hijab Bandung',
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_remember_me_checkbox_issues_a_remember_cookie(): void
    {
        $this->seedAdmin();

        // Checkbox mengirim value "1", sehingga remember-me harus aktif.
        $response = $this->post('/login', [
            'login' => 'admin@vendorhijabbandung.com',
            'password' => 'password123',
            'remember' => '1',
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertCookie(auth()->guard('web')->getRecallerName());
    }

    public function test_user_seeder_is_idempotent(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(UserSeeder::class);

        $this->assertSame(
            1,
            User::query()->where('email', 'admin@vendorhijabbandung.com')->count()
        );
    }

    /**
     * Seed the admin account exactly like production seeding does, so these
     * tests fail if the seeder credentials and the login flow ever drift.
     */
    private function seedAdmin(): User
    {
        $this->seed(UserSeeder::class);

        return User::query()->where('email', 'admin@vendorhijabbandung.com')->sole();
    }
}