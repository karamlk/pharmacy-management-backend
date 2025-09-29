<?php

namespace Tests\Feature\Supplier;

use App\Models\Medicine;
use App\Models\Supplier;
use App\Models\SupplierOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithAuthUser;

class SupplierOrderTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthUser;

    public function test_can_create_supplier_order_with_valid_data()
    {
        $this->actingAsPharmacist();

        $supplier = Supplier::factory()->create(['balance' => 0]);
        $medicine = Medicine::factory()->create(['quantity' => 10, 'price' => 5.00]);

        $payload = [
            'supplier_name' => $supplier->name,
            'items' => [
                ['name' => $medicine->name, 'quantity' => 3],
            ],
        ];

        $response = $this->postJson('/api/pharmacist/supplier-orders', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'The order was created successfully.',
                'total_price' => 15.00,
            ]);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'balance' => 15.00,
        ]);

        $this->assertDatabaseHas('supplier_orders', [
            'supplier_id' => $supplier->id,
            'total_price' => 15.00,
        ]);

        $this->assertDatabaseHas('supplier_order_items', [
            'medicine_id' => $medicine->id,
            'quantity' => 3,
            'unit_price' => 5.00,
        ]);

        $this->assertDatabaseHas('medicines', [
            'id' => $medicine->id,
            'quantity' => 13,
        ]);
    }

    public function test_cannot_create_supplier_order_with_missing_fields()
    {
        $this->actingAsPharmacist();
        $payload = [
            'items' => [
                ['name' => 'Amoxicillin', 'quantity' => 12]
            ]
        ];

        $response = $this->postJson('/api/pharmacist/supplier-orders', $payload);

        $response->assertStatus(422);
        $this->assertDatabaseCount('supplier_orders', 0);
    }

    public function test_cannot_create_supplier_order_with_invalid_data()
    {
        $this->actingAsPharmacist();
        Supplier::factory()->create(['name' => 'Pfizer']);
        $payload = [
            'supplier_name' => 'supp',
            'items' => ['name' => 'Amoxicillin', 'quantity' => 12]
        ];
        $response = $this->postJson('/api/pharmacist/supplier-orders', $payload);

        $response->assertStatus(422);
        $this->assertDatabaseEmpty('supplier_orders');
    }

    public function test_can_view_all_supplier_orders()
    {
        $this->actingAsPharmacist();
        SupplierOrder::factory(10)->create();

        $response = $this->getJson('/api/pharmacist/supplier-orders');

        $response->assertOk()
            ->assertJsonCount(10, 'data');
    }

    public function test_can_view_the_details_of_supplier_order_by_id()
    {
        $this->actingAsPharmacist();
        $supp_order = SupplierOrder::factory()->create();

        $response = $this->getJson("/api/pharmacist/supplier-orders/{$supp_order->id}");

        $response->assertOk();
    }

    public function test_can_view_orders_by_supplier_id()
    {
        $this->actingAsAdmin();
        $supplier = Supplier::factory()->create();
        SupplierOrder::factory(5)->create();
        SupplierOrder::factory(10)->create(['supplier_id' => $supplier->id]);

        $response = $this->getJson("/api/admin/supplier-orders/by-supplier/{$supplier->id}");

        $response->assertOk()
            ->assertJsonCount(10, 'data');
    }

    public function test_returns_404_for_nonexistent_supplier_order()
    {
        $this->actingAsPharmacist();

        $response = $this->getJson("/api/pharmacist/supplier-orders/10000");

        $response->assertStatus(404);
    }
}