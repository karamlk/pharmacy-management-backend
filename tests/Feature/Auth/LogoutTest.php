<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithAuthUser;

class LogoutTest extends TestCase
{
    use WithAuthUser;
    use RefreshDatabase;

    public function test_successful_logout(): void
    {
        $user = $this->createPharmacist();
        $token = $user->createToken('TestToken88')->plainTextToken;

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/pharmacist/logout');

        $response->assertOk()
            ->assertJson(['message' => 'LogOut Successfully']);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

}
