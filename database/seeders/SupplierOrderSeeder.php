<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\Supplier;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = Supplier::all();
        $pharmacists = User::whereHas('roles', function ($q) {
            $q->where('name', 'pharmacist');
        })->get();
        $medicines = Medicine::all();

        if ($suppliers->isEmpty() || $medicines->isEmpty() || $pharmacists->isEmpty()) {
            $this->command->warn("⚠️ Make sure suppliers, medicines, and pharmacist users exist before seeding orders.");
            return;
        }

        foreach ($suppliers as $supplier) {
            // Create 2 sample orders per supplier
            for ($i = 0; $i < 2; $i++) {
                $pharmacist = $pharmacists->random(); // pick a random pharmacist

                $order = SupplierOrder::create([
                    'supplier_id' => $supplier->id,
                    'user_id' => $pharmacist->id,
                    'order_date' => Carbon::now()->subDays(rand(1, 15)),
                    'total_price' => 0,
                ]);

                $totalPrice = 0;

                // Attach 2–4 medicines per order
                $randomMeds = $medicines->random(rand(2, 4));
                foreach ($randomMeds as $medicine) {
                    $quantity = rand(5, 20);
                    $unitPrice = $medicine->price;

                    SupplierOrderItem::create([
                        'supplier_order_id' => $order->id,
                        'medicine_id' => $medicine->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                    ]);

                    $totalPrice += $quantity * $unitPrice;
                }

                // Update order total price
                $order->update(['total_price' => $totalPrice]);
            }
        }
    }
}
