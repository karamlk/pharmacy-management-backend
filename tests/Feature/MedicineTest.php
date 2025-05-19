<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MedicineTest extends TestCase
{
 use RefreshDatabase;

    public function test_it_creates_medicine_with_valid_data(): void
    {
        $category = Category::factory()->create(['name' => 'Antibiotics']);

        $response = $this->postJson('/api/medicines', [
            'name' => 'Amoxicillin',
            'category_name' => $category->name,
            'manufacturer' => 'Pfizer',
            'price' => 20.5,
            'stock' => 100,
            'expiry_date' => '2025-12-31',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('medicines', ['name' => 'Amoxicillin']);
    }
    public function test_it_requires_category_name_to_exist()
    {
        $response = $this->postJson('/api/medicines', [
            'name' => 'Aspirin',
            'category_name' => 'Nonexistent Category',
            'manufacturer' => 'Generic',
            'price' => 10.00,
            'stock' => 50,
            'expiry_date' => '2025-12-31',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category_name']);
    }

    public function test_a_category_can_have_many_medicines()
{
    $category = Category::factory()->create();

    $category->medicines()->createMany([
        ['name' => 'Ibuprofen', 'manufacturer' => 'Bayer', 'price' => 10, 'stock' => 50, 'expiry_date' => '2026-01-01'],
        ['name' => 'Paracetamol', 'manufacturer' => 'GSK', 'price' => 5, 'stock' => 200, 'expiry_date' => '2026-06-01'],
    ]);

    $this->assertCount(2, $category->medicines);
}
}
