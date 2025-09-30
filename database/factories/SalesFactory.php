<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sales>
 */
class SalesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'invoice_number' => $this->faker->unique()->numerify('INV-#####'),
            'invoice_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'total_price' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }
}
