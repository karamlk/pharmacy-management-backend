<?php

namespace Tests\Feature\Medicine;

use App\Models\Medicine;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithAuthUser;

class MedicineFiltersTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthUser;

    public function test_expired_medicines_are_filtered_correctly()
    {
        $this->actingAsPharmacist();
        Medicine::factory(10)->create([
            'expiry_date' => Carbon::now()->subDays(30)
        ]);
        Medicine::factory(5)->create([
            'expiry_date' => Carbon::now()->addDays(30)
        ]);

        $response = $this->getJson("/api/pharmacist/medicines/expired");

        $response->assertOk()
            ->assertJsonCount(10, 'data');
    }

    public function test_out_of_stock_medicines_are_filtered_correctly()
    {
        $this->actingAsPharmacist();
        Medicine::factory(10)->create([
            'quantity' => 0
        ]);
        Medicine::factory(5)->create([
            'quantity' => 30
        ]);

        $response = $this->getJson("/api/pharmacist/medicines/outOfStock");

        $response->assertOk()
            ->assertJsonCount(10, 'data');
    }

    public function test_can_search_medicines()
    {
        $this->actingAsPharmacist();
        Medicine::factory(5)->create([
            'price' => 40.00
        ]);
        Medicine::factory(10)->create([
            'price' => 80.00
        ]);

        $respons = $this->postJson('/api/pharmacist/medicines/search', [
            'min_price' => 30,
            'max_price' => 50
        ]);

        $respons->assertOk()
            ->assertJsonCount(5, 'data');
    }
}
