<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Base test case for feature tests that talk to the auth-protected routes
 * (dashboard, resources and reports). A fresh user is created and signed in
 * automatically so every request passes the `auth` middleware.
 */
abstract class AuthenticatedTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->actingAs($this->user);
    }
}