<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\SaleItem;
use App\Models\Sales;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pharmacists = User::whereHas('roles', function ($q) {
            $q->where('name', 'pharmacist');
        })->get();

        $medicines = Medicine::all();

        if ($pharmacists->isEmpty() || $medicines->isEmpty()) {
            $this->command->warn("⚠️ Make sure pharmacists and medicines exist before seeding sales.");
            return;
        }

        // Create 10 random sales
        for ($i = 0; $i < 50; $i++) {
            $pharmacist = $pharmacists->random();

            $sale = Sales::create([
                'user_id' => $pharmacist->id,
                'invoice_number' => 'INV-' . strtoupper(Str::random(6)),
                'invoice_date' => Carbon::now()->subDays(rand(0, 15))->setTime(rand(9, 20), rand(0, 59)),
                'total_price' => 0,
            ]);

            $totalPrice = 0;

            // Each sale has 1–5 medicines
            $randomMeds = $medicines->random(rand(1, 5));
            foreach ($randomMeds as $medicine) {
                $quantity = rand(1, 3); // usually smaller than supplier orders
                $unitPrice = $medicine->price;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'medicine_id' => $medicine->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ]);

                $totalPrice += $quantity * $unitPrice;

                // Decrease stock (simulate real sale)
                $medicine->decrement('quantity', $quantity);
            }

            $sale->update(['total_price' => $totalPrice]);
        }
    }
}
