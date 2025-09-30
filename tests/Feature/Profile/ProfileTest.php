<?php

namespace Tests\Feature\Profile;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithAuthUser;

class ProfileTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthUser;

    public function test_authenticated_user_can_view_profile()
    {
        $this->actingAsPharmacist();

        $response = $this->getJson("/api/pharmacist/profile");

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'email'
            ]);
    }

    public function test_unauthenticated_user_cannot_view_profile()
    {

        $response = $this->getJson("/api/pharmacist/profile");

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_update_profile_with_valid_data()
    {
        $pharmacist = User::factory()->create(['name' => 'john', 'email' => 'ph1@example.com']);
        $role = Role::firstOrCreate(['name' => 'pharmacist']);
        $pharmacist->roles()->syncWithoutDetaching([$role->id]);

        $response = $this->actingAs($pharmacist)->putJson("/api/pharmacist/profile", [
            'name' => 'steve',
            'email' => 'ph2@example.com'
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'email'
            ]);

        $this->assertDatabaseHas('users',[
            'id' => $pharmacist->id,
            'name' => 'steve',
            'email' => 'ph2@example.com'
        ]);
    }
    public function test_profile_update_does_not_allow_email_duplication()
    {
        $user=User::factory()->create(['name' => 'leo', 'email' => 'ph2@example.com']);
        $pharmacist = User::factory()->create(['name' => 'john', 'email' => 'ph1@example.com']);
        $role = Role::firstOrCreate(['name' => 'pharmacist']);
        $pharmacist->roles()->syncWithoutDetaching([$role->id]);



        $response = $this->actingAs($pharmacist)->putJson("/api/pharmacist/profile", [
            'name' => 'steve',
            'email' => 'ph2@example.com'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseMissing('users',[
            'id' => $pharmacist->id,
            'name' => 'steve',
            'email' => 'ph2@example.com'
        ]);
    }
}
