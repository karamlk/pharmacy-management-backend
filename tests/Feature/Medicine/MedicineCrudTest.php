<?php

namespace Tests\Feature\Medicine;

use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithAuthUser;

class MedicineCrudTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthUser;

    public function test_can_create_medicine_with_valid_data()
    {
        $this->actingAsPharmacist();
        $category = Category::factory()->create(['name' => 'Antibiotics']);
        $medicine = [
            'category_name' => $category->name,
            "name" => "Paracetamol",
            "barcode" => "73413487987345",
            "manufacturer" => "Pfizer",
            "active_ingredient" => "Acetaminophen",
            "price" => 2.50,
            "quantity" => 100,
            "production_date" => "2024-06-01",
            "expiry_date" => "2026-06-01",
        ];

        $response = $this->postJson('/api/pharmacist/medicines', $medicine);

        $response->assertStatus(201);
        $this->assertDatabaseHas('medicines', ['barcode' => '73413487987345']);
    }

    public function test_cannot_create_medicine_without_barcode()
    {
        $this->actingAsPharmacist();

        $category = Category::factory()->create(['name' => 'Antibiotics']);

        $medicine = [
            'category_name' => $category->name,
            'name' => 'Paracetamol',
            'manufacturer' => 'Pfizer',
            'active_ingredient' => 'Acetaminophen',
            'price' => 2.50,
            'quantity' => 100,
            'production_date' => '2024-06-01',
            'expiry_date' => '2026-06-01',
        ];

        $response = $this->postJson('/api/pharmacist/medicines', $medicine);

        $response->assertStatus(422);
        
    }

    public function test_cannot_create_duplicate_medicine_barcode()
    {
        $this->actingAsPharmacist();
        $category = Category::factory()->create(['name' => 'Antibiotics']);
        $medicine1 = [
            'category_name' => $category->name,
            "name" => "Paracetamol",
            "barcode" => "73413487987345",
            "manufacturer" => "Pfizer",
            "active_ingredient" => "Acetaminophen",
            "price" => 2.50,
            "quantity" => 100,
            "production_date" => "2024-06-01",
            "expiry_date" => "2026-06-01",
        ];
        $medicine2 = $medicine1;

        $response = $this->postJson('/api/pharmacist/medicines', $medicine1);

        $response->assertStatus(201);

        $response = $this->postJson('/api/pharmacist/medicines', $medicine2);

        $this->assertDatabaseCount('medicines', 1);
    }

    public function test_can_view_all_medicines()
    {
        $this->actingAsPharmacist();
        Medicine::factory(10)->create();

        $response = $this->getJson('/api/pharmacist/medicines');

        $response->assertOk();
        $response->assertJsonCount(10, 'data');
        $this->assertDatabaseCount('medicines', 10);
    }

    public function test_can_view_single_medicine_by_id()
    {
        $this->actingAsPharmacist();
        $medicine = Medicine::factory()->create();

        $response = $this->getJson("/api/pharmacist/medicines/{$medicine->id}");

        $response->assertOk();
        $response->assertJsonFragment([
            'barcode' => $medicine->barcode,
        ]);
    }


    public function test_can_update_medicine()
    {
        $this->actingAsPharmacist();
        $medicine = Medicine::factory()->create(['barcode' => '325235235255', 'name' => 'Vitamin C']);


        $response = $this->putJson("/api/pharmacist/medicines/{$medicine->id}", [
            'name' => 'Vitamin D',
            'barcode' => $medicine->barcode,
            'manufacturer' => $medicine->manufacturer,
            'active_ingredient' => $medicine->active_ingredient,
            'price' => $medicine->price,
            'quantity' => $medicine->quantity,
            'production_date' => $medicine->production_date,
            'expiry_date' => $medicine->expiry_date,
            'category_name' => $medicine->category->name,
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => 'Vitamin D',
            'barcode' => $medicine->barcode,
        ]);

        $this->assertDatabaseHas('medicines', [
            'id' => $medicine->id,
            'barcode' => $medicine->barcode,
            'name' => 'Vitamin D',
        ]);
    }


    public function test_cannot_update_medicine_with_invalid_data()
    {
        $this->actingAsPharmacist();
        $medicine = Medicine::factory()->create();


        $response = $this->putJson("/api/pharmacist/medicines/{$medicine->id}", [
            'name' => 'Vitamin D',
            'barcode' => $medicine->barcode,
            'manufacturer' => $medicine->manufacturer,
            'active_ingredient' => $medicine->active_ingredient,
            'price' => -100.00,
            'quantity' => $medicine->quantity,
            'production_date' => $medicine->production_date,
            'expiry_date' => $medicine->expiry_date,
            'category_name' => $medicine->category->name,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('price');
    }


    public function test_can_soft_delete_medicine()
    {
        $this->actingAsPharmacist();
        $medicine = Medicine::factory()->create();

        $response = $this->deleteJson("/api/pharmacist/medicines/{$medicine->id}");

        $response->assertOk();
        $this->assertSoftDeleted('medicines', [
            'id' => $medicine->id,
        ]);
    }

    public function test_soft_deleted_medicine_is_not_visible_in_list()
    {
        $this->actingAsPharmacist();
        $medicine = Medicine::factory()->create();

        $response = $this->deleteJson("/api/pharmacist/medicines/{$medicine->id}");

        $response->assertOk();
        $this->assertSoftDeleted('medicines', [
            'id' => $medicine->id,
        ]);

        $response = $this->getJson("/api/pharmacist/medicines");

        $response->assertOk();
        $response->assertJsonMissing(['id' => $medicine->id]);
    }

    public function test_returns_404_when_deleting_nonexistent_medicine()
    {
        $this->actingAsPharmacist();

        $response = $this->deleteJson("/api/pharmacist/medicines/10000");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Medicine not found']);
    }
}
