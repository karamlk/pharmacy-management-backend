<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Supplier::create([
            'name' => 'Pfizer',
            'email' => 'contact@pfizer.com',
            'phone' => '1234567890',
            'address' => 'New York, USA',
            'balance' => 1500.00,
        ]);

        Supplier::create([
            'name' => 'Novartis',
            'email' => 'support@novartis.com',
            'phone' => '9876543210',
            'address' => 'Basel, Switzerland',
            'balance' => 2200.50,
        ]);

        Supplier::create([
            'name' => 'Sanofi',
            'email' => 'info@sanofi.com',
            'phone' => '1122334455',
            'address' => 'Paris, France',
            'balance' => 500.00,
        ]);
    }
}
