<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\WithAuthUser;

class CategoryTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthUser;

    public function test_can_get_all_categories()
    {
        $this->actingAsPharmacist();

        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/pharmacist/categories');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_category()
    {
        $this->actingAsPharmacist();

        $data = [
            'id'=>3,
            'name' => 'Painkillers',
        ];

        $response = $this->postJson('/api/pharmacist/categories', $data);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Painkillers');

        $this->assertDatabaseHas('categories', [
            'name' => 'Painkillers'
        ]);
    }

    public function test_cannot_create_duplicate_category()
    {
        $this->actingAsPharmacist();

        Category::factory()->create([
            'name' => 'Antibiotics'
        ]);

        $response = $this->postJson('/api/pharmacist/categories', [
            'name' => 'Antibiotics'
        ]);

        $response->assertStatus(422);
    }

    public function test_can_update_category()
    {
        $this->actingAsPharmacist();

        $category = Category::factory()->create([
            'name' => 'Old Name'
        ]);

        $response = $this->putJson("/api/pharmacist/categories/{$category->id}", [
            'name' => 'New Name'
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Name'
        ]);
    }

    public function test_cannot_update_to_existing_name()
    {
        $this->actingAsPharmacist();

        Category::factory()->create(['name' => 'Existing']);
        $category = Category::factory()->create(['name' => 'Another']);

        $response = $this->putJson("/api/pharmacist/categories/{$category->id}", [
            'name' => 'Existing'
        ]);

        $response->assertStatus(422);
    }

    public function test_can_delete_category_and_reassign_medicines()
    {
        $this->actingAsPharmacist();

        $category = Category::factory()->create(['name' => 'ToDelete']);
        $uncategorized = Category::factory()->create(['name' => 'uncategorized']);

        $medicine = \App\Models\Medicine::factory()->create([
            'category_id' => $category->id
        ]);

        $response = $this->deleteJson("/api/pharmacist/categories/{$category->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);

        $this->assertDatabaseHas('medicines', [
            'id' => $medicine->id,
            'category_id' => $uncategorized->id
        ]);
    }

    public function test_delete_fails_if_uncategorized_missing()
    {
        $this->actingAsPharmacist();

        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/pharmacist/categories/{$category->id}");

        $response->assertStatus(500);
    }
}
