<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Medicine>
 */
class MedicineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
         

        return [
            'category_id' => Category::factory(),
            'name' => $this->faker->unique()->word(),
            'barcode' => $this->faker->unique()->ean13(),
            'manufacturer' => $this->faker->company(),
            'active_ingredient' => $this->faker->word(),
            'price' => $this->faker->randomFloat(2, 5, 500),
            'quantity' => $this->faker->numberBetween(0, 1000),
            'production_date' => $this->faker->dateTimeBetween('-2 years', '-6 months')->format('Y-m-d'),
            'expiry_date' => $this->faker->dateTimeBetween('now', '+2 years')->format('Y-m-d'),

        ];
    }
}
