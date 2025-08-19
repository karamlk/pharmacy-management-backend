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
            ['Paracetamol', 'Pfizer', 'Acetaminophen', 2.50, 100],
            ['Amoxicillin', 'GlaxoSmithKline', 'Amoxicillin', 5.00, 80],
            ['Vitamin C', 'Nature’s Way', 'Ascorbic Acid', 1.20, 200],
            ['Cetirizine', 'Zyrtec', 'Cetirizine Hydrochloride', 3.00, 50],
            ['med-5', 'x', 'Ingredient-5', 3.00, 120],
            ['med-6', 'y', 'Ingredient-6', 9.00, 110],
            ['med-7', 'z', 'Ingredient-7', 1.60, 100],
            ['med-8', 'xy', 'Ingredient-8', 7.20, 140],
        ];

        foreach ($sampleMedicines as $i => $data) {
            Medicine::create([
                'name' => $data[0],
                'manufacturer' => $data[1],
                'active_ingredient' => $data[2],
                'price' => $data[3],
                'quantity' => $data[4],
                'production_date' => Carbon::now()->subMonths(rand(1, 12)),
                'expiry_date' => Carbon::now()->addMonths(rand(6, 24)),
                'img_url' => '/images/default_medicine.jpg',
                'category_id' => $categories[$i % $categories->count()]->id,
                'barcode' => $this->generateBarcode(),
            ]);
        }
    }

    private function generateBarcode(): string
    {
        $barcode = '';
        for ($i = 0; $i < 13; $i++) {
            $barcode .= mt_rand(0, 9);
        }
        return $barcode;
    }
}
