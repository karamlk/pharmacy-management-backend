<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pharmacists = User::whereHas('roles', function ($q) {
            $q->where('name', 'pharmacist');
        })->get();

        $suppliers = Supplier::all();

        if ($pharmacists->isEmpty() || $suppliers->isEmpty()) {
            $this->command->warn("⚠️ Make sure pharmacists and suppliers exist before seeding payments.");
            return;
        }

        // Create 15 random supplier payments
        for ($i = 0; $i < 15; $i++) {
            $pharmacist = $pharmacists->random();
            $supplier = $suppliers->random();
            // Skip zero or negative balances
            if ($supplier->balance <= 0) {
                continue;
            }

            // Cap the payment at the available balance
            $amount = min($supplier->balance, rand(200, 600));

            // If the random cap results in zero, skip
            if ($amount <= 0) {
                continue;
            }
            $paymentDate = Carbon::now()->subDays(rand(1, 60));

            SupplierPayment::create([
                'supplier_id' => $supplier->id,
                'user_id' => $pharmacist->id,
                'amount' => $amount,
                'payment_date' => $paymentDate,
            ]);

            // Decrease supplier balance (they received money)
            $supplier->decrement('balance', $amount);
        }
    }
}
