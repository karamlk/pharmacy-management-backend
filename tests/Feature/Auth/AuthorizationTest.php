<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithAuthUser;

class AuthorizationTest extends TestCase
{
    use WithAuthUser;
    use RefreshDatabase;

    public function test_pharmacist_cannot_access_admin_routes(): void
    {
        $this->actingAsPharmacist();

        $response = $this->getJson('/api/admin/user-sessions');

        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_pharmacist_routes(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/pharmacist/sales');

        $response->assertStatus(403);
    }

    public function test_Unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/pharmacist/sales');

        $response->assertStatus(401);
    }

    public function test_invalid_token_rejected(): void
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer invalid_token'])
            ->getJson('/api/pharmacist/medicines');

        $response->assertStatus(401);
    }
}
