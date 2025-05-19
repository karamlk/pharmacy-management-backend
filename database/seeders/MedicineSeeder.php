<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->command->warn("No categories found. Run CategorySeeder first.");
            return;
        }

        $sampleMedicines = [
            ['Paracetamol', 'Pfizer', 2.50, 100],
            ['Amoxicillin', 'GlaxoSmithKline', 5.00, 80],
            ['Vitamin C', 'Nature’s Way', 1.20, 200],
            ['Cetirizine', 'Zyrtec', 3.00, 50],
        ];

        foreach ($sampleMedicines as $i => $data) {
            Medicine::create([
                'name' => $data[0],
                'manufacturer' => $data[1],
                'price' => $data[2],
                'stock' => $data[3],
                'expiry_date' => Carbon::now()->addMonths(rand(6, 24)),
                'category_id' => $categories[$i % $categories->count()]->id,
            ]);
        }
    }
}
