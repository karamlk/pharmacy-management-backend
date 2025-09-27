<?php

namespace Tests\Feature\Supplier;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithAuthUser;

class SupplierCrudTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthUser;

    public function test_can_create_supplier_with_valid_data(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson("/api/admin/suppliers", [
            'name' => 'Pfizer',
            'email' => 'contact@pfizer.com',
            'phone' => '1234567890',
            'address' => 'New York, USA',
            'balance' => 1500.00,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Pfizer']);
        $this->assertDatabaseHas(
            'suppliers',
            ['name' => 'Pfizer']
        );
    }

    public function test_cannot_create_supplier_with_missing_fields(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson("/api/admin/suppliers", [
            'email' => 'contact@pfizer.com',
            'phone' => '1234567890',
            'address' => 'New York, USA',
            'balance' => 1500.00,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_can_view_all_suppliers()
    {
        $this->actingAsAdmin();
        Supplier::factory(10)->create();

        $response = $this->getJson("/api/admin/suppliers");

        $response->assertOk()
            ->assertJsonCount(10, 'data');
    }

    public function test_can_view_single_supplier_by_id()
    {
        $this->actingAsAdmin();
        $supplier = Supplier::factory()->create();

        $response = $this->getJson("/api/admin/suppliers/{$supplier->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $supplier->id]);
    }

    public function test_returns_404_for_nonexistent_supplier(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson("/api/admin/suppliers/10000");

        $response->assertStatus(404);
    }

    public function test_can_update_supplier_with_valid_data(): void
    {
        $this->actingAsAdmin();
        $supplier = Supplier::factory()->create(['email'=>'ex@email.com']);

        $response = $this->putJson("/api/admin/suppliers/{$supplier->id}", [
            'email' => 'contact@pfizer.com',
            'phone' => '1234567890',
            'address' => 'New York, USA',
            'balance' => 1500.00,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'email' => 'contact@pfizer.com',
            'phone' => '1234567890',
        ]);
    }

    public function test_cannot_update_supplier_with_invalid_data(): void
    {
        $this->actingAsAdmin();
        $supplier = Supplier::factory()->create();

        $response = $this->putJson("/api/admin/suppliers/{$supplier->id}", [
            'email' => 'contact@pfizer.com',
            'phone' => '1234567890',
            'address' => 'New York, USA',
            'balance' => -1500.00,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('balance');
    }

    public function test_returns_404_when_updating_nonexistent_supplier(): void
    {
        $this->actingAsAdmin();

        $response = $this->putJson("/api/admin/suppliers/10000");

        $response->assertStatus(404);
    }

    public function test_can_delete_supplier(): void
    {
        $this->actingAsAdmin();
        $supplier = Supplier::factory()->create();

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);

        $response = $this->deleteJson("/api/admin/suppliers/{$supplier->id}");

        $response->assertOk();

        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    public function test_returns_404_when_deleting_nonexistent_supplier(): void
    {
        $this->actingAsAdmin();

        $response = $this->deleteJson("/api/admin/suppliers/10000");

        $response->assertStatus(404);
    }
}
