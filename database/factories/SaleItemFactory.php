<?php

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\Sales;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaleItem>
 */
class SaleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $medicine = Medicine::factory()->create(['quantity' => 100]);
        $quantity = $this->faker->numberBetween(1, 10);
        $unitPrice = $medicine->price ?? $this->faker->randomFloat(2, 5, 100);

        return [
            'sale_id' => Sales::factory(),
            'medicine_id' => $medicine->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ];
    }
}
