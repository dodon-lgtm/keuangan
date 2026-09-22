<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Coverage for the "Ganti Password" form inside the navbar settings modal:
 * hashing of the stored password, the JSON responses consumed by the modal
 * (fetch, no page reload), the redirect + flash fallback for plain posts and
 * the markup/JS hooks the modal script depends on.
 */
class PasswordChangeTest extends AuthenticatedTestCase
{
    /** Password baru yang valid (minimal 8 karakter). */
    private const NEW_PASSWORD = 'PasswordBaru123';

    /** Password default UserFactory. */
    private const CURRENT_PASSWORD = 'password';

    public function test_guest_is_redirected_to_login(): void
    {
        Auth::logout();

        $this->put('/settings/password', [
            'current_password' => self::CURRENT_PASSWORD,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertRedirectToRoute('login');
    }

    public function test_ajax_request_updates_password_with_hash_and_json_success(): void
    {
        $oldHash = $this->user->password;

        $response = $this->putJson(route('settings.password'), [
            'current_password' => self::CURRENT_PASSWORD,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password berhasil diperbarui.',
            ])
            ->assertJsonStructure(['csrf_token']);

        // session()->regenerate() merotasi CSRF token; modal memakai nilai ini
        // untuk menyinkronkan token semua form di halaman (tanpa itu submit
        // berikutnya akan kena 419 CSRF token mismatch).
        $this->assertNotEmpty($response->json('csrf_token'));

        $fresh = $this->user->fresh();

        // Hash baru, bukan password mentah dan bukan hasil double-hash.
        $this->assertNotSame($oldHash, $fresh->password);
        $this->assertNotSame(self::NEW_PASSWORD, $fresh->password);
        $this->assertTrue(Hash::isHashed($fresh->password));
        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $fresh->password));

        // Sesi tetap valid setelah session di-regenerate.
        $this->get('/dashboard')->assertStatus(200);

        // Password lama sudah tidak berlaku.
        $this->assertFalse(Hash::check(self::CURRENT_PASSWORD, $fresh->password));
    }

    public function test_ajax_request_rejects_wrong_current_password(): void
    {
        $hash = $this->user->password;

        $this->putJson(route('settings.password'), [
            'current_password' => 'password-salah',
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['current_password'])
            ->assertJsonPath('errors.current_password.0', 'Password saat ini salah.');

        $this->assertSame($hash, $this->user->fresh()->password);
    }

    public function test_ajax_request_rejects_mismatched_confirmation(): void
    {
        $hash = $this->user->password;

        $this->putJson(route('settings.password'), [
            'current_password' => self::CURRENT_PASSWORD,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => 'PasswordLain123',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password'])
            ->assertJsonPath('errors.password.0', 'Password belum cocok.');

        $this->assertSame($hash, $this->user->fresh()->password);
    }

    public function test_ajax_request_enforces_minimum_password_length(): void
    {
        $this->putJson(route('settings.password'), [
            'current_password' => self::CURRENT_PASSWORD,
            'password' => 'pendek',
            'password_confirmation' => 'pendek',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password'])
            ->assertJsonPath('errors.password.0', 'Password baru minimal 8 karakter.');
    }

    public function test_plain_post_redirects_back_with_success_flash(): void
    {
        $this->from('/dashboard')->put(route('settings.password'), [
            'current_password' => self::CURRENT_PASSWORD,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])
            ->assertRedirect('/dashboard')
            ->assertSessionHas('success', 'Password berhasil diperbarui.');

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $this->user->fresh()->password));
    }

    public function test_plain_post_with_wrong_current_password_redirects_back_with_errors(): void
    {
        $this->from('/dashboard')->put(route('settings.password'), [
            'current_password' => 'password-salah',
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertRedirectBackWithErrors(['current_password']);
    }

    public function test_validation_errors_are_rendered_inside_the_modal(): void
    {
        $response = $this->from('/dashboard')->put(route('settings.password'), [
            'current_password' => 'password-salah',
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ]);

        $response->assertRedirectBackWithErrors(['current_password']);

        // Setelah redirect, pesan error tampil di dalam modal dan form otomatis dibuka.
        $this->followRedirects($response)
            ->assertStatus(200)
            ->assertSee('Password saat ini salah.')
            ->assertSee('showForm();', false)
            ->assertSee('openModal();', false);
    }

    public function test_settings_modal_renders_the_hooks_used_by_the_script(): void
    {
        $this->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('id="settingsModal"', false)
            ->assertSee('id="passwordForm"', false)
            ->assertSee('action="'.route('settings.password').'"', false)
            ->assertSee('id="currentPasswordError"', false)
            ->assertSee('id="passwordError"', false)
            ->assertSee('id="passwordConfirmationError"', false)
            ->assertSee('id="passwordSubmitBtn"', false)
            ->assertSee('id="pwMatch"', false)
            ->assertSee('id="appToast"', false)
            ->assertSee('id="appToastClose"', false)
            ->assertSee('minlength="8"', false)
            ->assertSee('syncCsrfToken(', false)
            ->assertSee('Password belum cocok', false);
    }
}
