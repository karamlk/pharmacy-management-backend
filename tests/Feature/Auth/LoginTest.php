<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithAuthUser;

class LoginTest extends TestCase
{

    use WithAuthUser;
    use RefreshDatabase;


    public function test_successful_login(): void
    {
        $user = $this->createPharmacist();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response->assertStatus(200);
    }

    public function test_failed_login(): void
    {
        $user = $this->createPharmacist();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong password'
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'the provided credentials are incorrect'
        ]);
    }
}
