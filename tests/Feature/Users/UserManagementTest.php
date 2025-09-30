<?php

namespace Tests\Feature\Users;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\Traits\WithAuthUser;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthUser;

    public function test_can_create_pharmacist_with_valid_data()
    {
        $this->actingAsAdmin();
        Role::factory()->create(['name' => 'pharmacist']);

        $response = $this->postJson("/api/admin/pharmacists", [
            'name' => 'pharm-1',
            'email' => 'ph1@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'hourly_rate' => 22.50
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['message' => 'Pharmacist created successfully'])
            ->assertJsonFragment(['email' => 'ph1@example.com']);


        $this->assertDatabaseHas('users', [
            'email' => 'ph1@example.com'
        ]);

        $this->assertDatabaseHas('role_user', [
            'role_id' => Role::where('name', 'pharmacist')->first()->id,
            'user_id' => User::where('email', 'ph1@example.com')->first()->id
        ]);
    }

    public function test_cannot_create_pharmacist_with_missing_fields()
    {
        $this->actingAsAdmin();
        Role::factory()->create(['name' => 'pharmacist']);

        $response = $this->postJson("/api/admin/pharmacists", [
            'name' => 'pharm-1',
            'password' => 'password',
            'password_confirmation' => 'password',
            'hourly_rate' => 22.50
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseMissing('users', [
            'email' => 'ph1@example.com'
        ]);
    }

    public function test_cannot_create_pharmacist_with_duplicate_email()
    {
        $this->actingAsAdmin();
        Role::factory()->create(['name' => 'pharmacist']);

        $response = $this->postJson("/api/admin/pharmacists", [
            'name' => 'pharm-1',
            'email' => 'ph1@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'hourly_rate' => 22.50
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['message' => 'Pharmacist created successfully'])
            ->assertJsonFragment(['email' => 'ph1@example.com']);


        $response = $this->postJson("/api/admin/pharmacists", [
            'name' => 'pharm-2',
            'email' => 'ph1@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'hourly_rate' => 22.50
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_can_view_all_pharmacists()
    {
        $this->actingAsAdmin();
        User::factory()->count(5)->pharmacist()->create();

        $response = $this->getJson("/api/admin/pharmacists");

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_can_view_single_pharmacist_by_id()
    {
        $this->actingAsAdmin();
        $pharmacist = User::factory()->pharmacist()->create();

        $response = $this->getJson("/api/admin/pharmacists/{$pharmacist->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $pharmacist->id]);
    }

    public function test_returns_404_for_nonexistent_pharmacist()
    {
        $this->actingAsAdmin();

        $response = $this->getJson("/api/admin/pharmacists/1000");

        $response->assertNotFound()
            ->assertJson(['error' => 'user not found']);
    }

    public function test_can_update_pharmacist_with_valid_data()
    {
        $this->actingAsAdmin();
        $pharmacist = User::factory()->pharmacist()->create([
            'name' => 'pharm-1',
            'email' => 'ph1@example.com',
            'password' => Hash::make('password'),
            'hourly_rate' => 22.50
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $pharmacist->id,
            'name' => 'pharm-1',
            'email' => 'ph1@example.com',
            'hourly_rate' => 22.50
        ]);

        $response = $this->putJson("/api/admin/pharmacists/{$pharmacist->id}", [
            'name' => 'pharm-2',
            'email' => 'ph2@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'hourly_rate' => 22.50
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $pharmacist->id,
            'name' => 'pharm-2',
            'email' => 'ph2@example.com',
            'hourly_rate' => 22.50
        ]);
    }

    public function test_cannot_update_pharmacist_with_invalid_data()
    {

        $this->actingAsAdmin();
        $pharmacist = User::factory()->pharmacist()->create([
            'name' => 'pharm-1',
            'email' => 'ph1@example.com',
            'password' => Hash::make('password'),
            'hourly_rate' => 22.50
        ]);

        $response = $this->putJson("/api/admin/pharmacists/{$pharmacist->id}", [
            'name' => 'pharm-1',
            'email' => 'ph1@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'hourly_rate' => -22.50
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('hourly_rate');

        $this->assertDatabaseHas('users', [
            'id' => $pharmacist->id,
            'name' => 'pharm-1',
            'email' => 'ph1@example.com',
            'hourly_rate' => 22.50
        ]);
    }

    public function test_can_delete_pharmacist()
    {
        $this->actingAsAdmin();
        $pharmacist = User::factory()->pharmacist()->create([
            'name' => 'pharm-1',
            'email' => 'ph1@example.com',
            'password' => Hash::make('password'),
            'hourly_rate' => 22.50
        ]);

        $response = $this->deleteJson("/api/admin/pharmacists/{$pharmacist->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Pharmacist deleted successfully']);

        $this->assertSoftDeleted('users', [
            'id' => $pharmacist->id,
            'name' => 'pharm-1',
            'email' => 'ph1@example.com',
            'hourly_rate' => 22.50
        ]);
    }
    
    public function test_returns_404_when_deleting_nonexistent_pharmacist()
    {
        $this->actingAsAdmin();

        $response = $this->deleteJson("/api/admin/pharmacists/1000");

        $response->assertNotFound()
            ->assertJson(['error' => 'user not found']);
    }
}
