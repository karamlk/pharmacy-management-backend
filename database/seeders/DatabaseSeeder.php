<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
          $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            UserSessionSeeder::class,
            SupplierSeeder::class,
            CategorySeeder::class,   
            MedicineSeeder::class,
            SupplierOrderSeeder::class,
            SalesSeeder::class,
            SupplierPaymentSeeder::class,
        ]);
    }
}
