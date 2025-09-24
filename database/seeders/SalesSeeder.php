<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\SaleItem;
use App\Models\Sales;
use App\Models\User;
use Carbon\Carbon;
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

        $today = Carbon::today();

        // Only valid medicines (not expired & in stock & not Uncategorized)
        $medicines = Medicine::where('category_id', '!=', 1)
            ->where('quantity', '>', 0)
            ->whereDate('expiry_date', '>', $today)
            ->get();

        if ($pharmacists->isEmpty() || $medicines->isEmpty()) {
            $this->command->warn(" Make sure pharmacists and valid medicines exist before seeding sales.");
            return;
        }

        // Create 50 random sales
        for ($i = 0; $i < 75; $i++) {
            $pharmacist = $pharmacists->random();

            $sale = Sales::create([
                'user_id' => $pharmacist->id,
                'invoice_number' => 'INV-' . strtoupper(Str::random(6)),
                'invoice_date' => Carbon::now()->subDays(rand(0, 15))->setTime(rand(9, 20), rand(0, 59)),
                'total_price' => 0,
            ]);

            $totalPrice = 0;

            // Pick 1–5 random medicines for this sale
            $randomMeds = $medicines->random(rand(1, 5));

            foreach ($randomMeds as $medicine) {
                if ($medicine->quantity <= 0) {
                    continue; // skip out-of-stock just in case
                }

                $quantity = rand(1, min(3, $medicine->quantity)); // don’t exceed stock
                $unitPrice = $medicine->price;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'medicine_id' => $medicine->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ]);

                $totalPrice += $quantity * $unitPrice;

                // Reduce stock
                $medicine->decrement('quantity', $quantity);
            }

            $sale->update(['total_price' => $totalPrice]);
        }
    }
}
