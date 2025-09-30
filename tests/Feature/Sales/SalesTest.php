<?php

namespace Tests\Feature\Sales;

use App\Models\Medicine;
use App\Models\SaleItem;
use App\Models\Sales;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithAuthUser;

class SalesTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthUser;

    public function test_can_create_sale_with_valid_data()
    {
        $pharmacist = $this->actingAsPharmacist();
        $med = Medicine::factory()->create(['name' => 'paracetamol', 'quantity' => 5, 'price' => 10.00]);

        $response = $this->postJson("/api/pharmacist/sales", [
            'items' => [
                [
                    'name' => 'paracetamol',
                    'quantity' => 2
                ]
            ]
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['message' => 'Sale recorded successfully.'])
            ->assertJsonStructure([
                'message',
                'invoice_number',
                'total_price'
            ]);

        $this->assertDatabaseHas('medicines', [
            'id' => $med->id,
            'name' => 'paracetamol',
            'quantity' => 3
        ]);

        $this->assertDatabaseHas('sales', [
            'user_id' => $pharmacist->id,
            'total_price' => 20.00
        ]);

        $this->assertDatabaseHas('sale_items', [
            'medicine_id' => $med->id,
            'unit_price' => 10.00,
            'quantity' => 2
        ]);
    }

    public function test_cannot_create_sale_with_missing_fields()
    {
        $this->actingAsPharmacist();
        Medicine::factory()->create(['name' => 'paracetamol', 'quantity' => 5, 'price' => 10.00]);

        $response = $this->postJson("/api/pharmacist/sales", [
            'items' => [
                [
                    'quantity' => 2
                ]
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['error', 'details']);

        $this->assertDatabaseCount('sales', 0);
    }

    public function test_cannot_create_sale_with_invalid_medicine_name()
    {
        $this->actingAsPharmacist();

        $response = $this->postJson("/api/pharmacist/sales", [
            'items' => [
                [
                    'name' => 'paracetamol',
                    'quantity' => 2
                ]
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['error']);

        $this->assertDatabaseCount('sales', 0);
    }

    public function test_cannot_create_sale_with_insufficient_stock()
    {
        $this->actingAsPharmacist();
        Medicine::factory()->create(['name' => 'paracetamol', 'quantity' => 5, 'price' => 10.00]);

        $response = $this->postJson("/api/pharmacist/sales", [
            'items' => [
                [
                    'name' => 'paracetamol',
                    'quantity' => 10
                ]
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['error']);

        $this->assertDatabaseCount('sales', 0);
    }

    public function test_can_view_all_sales()
    {
        $this->actingAsPharmacist();
        Sales::factory(10)->create();

        $response = $this->getJson("/api/pharmacist/sales");

        $response->assertOk()
            ->assertJsonCount(10, 'data');

        $this->assertDatabaseCount('sales', 10);
    }

    public function test_can_view_single_sale_by_id()
    {
        $this->actingAsPharmacist();
        $sale = Sales::factory()
            ->has(SaleItem::factory()->count(3), 'items')
            ->create();


        $response = $this->getJson("/api/pharmacist/sales/{$sale->id}");

        $response->assertOk();
    }

    public function test_returns_404_for_nonexistent_sale()
    {
        $this->actingAsPharmacist();

        $response = $this->getJson("/api/pharmacist/sales/1000");

        $response->assertNotFound();
    }
}
