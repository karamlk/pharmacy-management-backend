<?php

namespace Tests\Feature\Supplier;

use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithAuthUser;

class SupplierPaymentTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthUser;

    public function test_can_create_supplier_payment_with_valid_data()
    {
        $this->actingAsAdmin();
        $supplier = Supplier::factory()->create(['balance' => 100.00]);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'balance' => 100.00
        ]);

        $response = $this->postJson("/api/admin/suppliers/{$supplier->id}/payments", ['amount' => 20.00]);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Supplier payment created successfully.']);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'balance' => 80.00
        ]);

        $this->assertDatabaseHas('supplier_payments', [
            'supplier_id' => $supplier->id,
            'amount' => 20.00
        ]);
    }

    public function test_cannot_create_supplier_payment_with_missing_fields()
    {
        $this->actingAsAdmin();
        $supplier = Supplier::factory()->create(['balance' => 100.00]);

        $response = $this->postJson("/api/admin/suppliers/{$supplier->id}/payments",);

        $response->assertStatus(422)->assertJsonValidationErrors('amount');
        $this->assertDatabaseCount('supplier_payments', 0);
    }

    public function test_cannot_create_supplier_payment_for_nonexistent_supplier()
    {
        $this->actingAsAdmin();

        $response = $this->postJson("/api/admin/suppliers/1000/payments", ['amount' => 100.00]);

        $response->assertStatus(404);
        $this->assertDatabaseMissing('supplier_payments');
    }

    public function test_cannot_create_a_payment_which_it_is_amount_exceeds_the_supplier_balance()
    {
        $this->actingAsAdmin();
        $supplier = Supplier::factory()->create(['balance' => 100.00]);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'balance' => 100.00
        ]);

        $response = $this->postJson("/api/admin/suppliers/{$supplier->id}/payments", ['amount' => 200.00]);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Payment amount exceeds supplier balance.']);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'balance' => 100.00
        ]);

        $this->assertDatabaseMissing('supplier_payments', [
            'supplier_id' => $supplier->id,
            'amount' => 200.00
        ]);
    }

    public function test_can_view_all_supplier_payments()
    {
        $this->actingAsAdmin();
        SupplierPayment::factory(10)->create();

        $response = $this->getJson("/api/admin/supplier-payments");

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [  
                        'id',
                        'supplier_name',
                        'processed_by',
                        'amount_paid',
                        'payment_date',
                    ]
                ]
            ]);
        $this->assertDatabaseCount('supplier_payments', 10);
    }

    public function test_can_view_payments_for_specific_supplier()
    {
        $this->actingAsAdmin();
        $supplier = Supplier::factory()->create();
        SupplierPayment::factory(10)->create(['supplier_id' => $supplier->id]);

        $response = $this->getJson("/api/admin/suppliers/{$supplier->id}/payments");

        $response->assertOk()
            ->assertJsonCount(10, 'data');
    }
}
