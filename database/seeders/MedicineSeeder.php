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
        $categories = [
            'Pain Relief' => [
                ['name' => 'Paracetamol', 'manufacturer' => 'Tylenol', 'active' => 'Acetaminophen'],
                ['name' => 'Advil', 'manufacturer' => 'Pfizer', 'active' => 'Ibuprofen'],
                ['name' => 'Aspirin', 'manufacturer' => 'Bayer', 'active' => 'Acetylsalicylic acid'],
                ['name' => 'Aleve', 'manufacturer' => 'Bayer', 'active' => 'Naproxen'],
                ['name' => 'Excedrin', 'manufacturer' => 'Novartis', 'active' => 'Acetaminophen + Aspirin + Caffeine'],
                ['name' => 'Motrin', 'manufacturer' => 'Johnson & Johnson', 'active' => 'Ibuprofen'],
                ['name' => 'Voltaren', 'manufacturer' => 'Novartis', 'active' => 'Diclofenac'],
                ['name' => 'Celebrex', 'manufacturer' => 'Pfizer', 'active' => 'Celecoxib'],
                ['name' => 'Anacin', 'manufacturer' => 'Prestige Brands', 'active' => 'Aspirin + Caffeine'],
                ['name' => 'Midol', 'manufacturer' => 'Bayer', 'active' => 'Acetaminophen + Caffeine'],
            ],
            'Antibiotics' => [
                ['name' => 'Amoxicillin', 'manufacturer' => 'GlaxoSmithKline', 'active' => 'Amoxicillin'],
                ['name' => 'Augmentin', 'manufacturer' => 'GlaxoSmithKline', 'active' => 'Amoxicillin + Clavulanate'],
                ['name' => 'Ciprofloxacin', 'manufacturer' => 'Bayer', 'active' => 'Ciprofloxacin'],
                ['name' => 'Azithromycin', 'manufacturer' => 'Pfizer', 'active' => 'Azithromycin'],
                ['name' => 'Clarithromycin', 'manufacturer' => 'AbbVie', 'active' => 'Clarithromycin'],
                ['name' => 'Doxycycline', 'manufacturer' => 'Pfizer', 'active' => 'Doxycycline'],
                ['name' => 'Levofloxacin', 'manufacturer' => 'Daiichi Sankyo', 'active' => 'Levofloxacin'],
                ['name' => 'Metronidazole', 'manufacturer' => 'Sanofi', 'active' => 'Metronidazole'],
                ['name' => 'Penicillin V', 'manufacturer' => 'Pfizer', 'active' => 'Phenoxymethylpenicillin'],
                ['name' => 'Cephalexin', 'manufacturer' => 'Eli Lilly', 'active' => 'Cephalexin'],
            ],
            'Vitamins' => [
                ['name' => 'Centrum', 'manufacturer' => 'Pfizer', 'active' => 'Multivitamins'],
                ['name' => 'One A Day', 'manufacturer' => 'Bayer', 'active' => 'Multivitamins'],
                ['name' => 'Nature Made Vitamin D', 'manufacturer' => 'Pharmavite', 'active' => 'Cholecalciferol'],
                ['name' => 'Vitamin C', 'manufacturer' => 'NOW Foods', 'active' => 'Ascorbic Acid'],
                ['name' => 'Vitamin B12', 'manufacturer' => 'Nature’s Bounty', 'active' => 'Cobalamin'],
                ['name' => 'Vitamin A', 'manufacturer' => 'Solgar', 'active' => 'Retinol'],
                ['name' => 'Vitamin E', 'manufacturer' => 'Nature’s Bounty', 'active' => 'Alpha-Tocopherol'],
                ['name' => 'Calcium + Vitamin D', 'manufacturer' => 'Citracal', 'active' => 'Calcium Carbonate + Vitamin D3'],
                ['name' => 'Zinc Supplement', 'manufacturer' => 'Nature Made', 'active' => 'Zinc Gluconate'],
                ['name' => 'Magnesium Supplement', 'manufacturer' => 'NOW Foods', 'active' => 'Magnesium Oxide'],
            ],
            'Allergy' => [
                ['name' => 'Claritin', 'manufacturer' => 'Bayer', 'active' => 'Loratadine'],
                ['name' => 'Zyrtec', 'manufacturer' => 'Johnson & Johnson', 'active' => 'Cetirizine'],
                ['name' => 'Allegra', 'manufacturer' => 'Sanofi', 'active' => 'Fexofenadine'],
                ['name' => 'Benadryl', 'manufacturer' => 'Pfizer', 'active' => 'Diphenhydramine'],
                ['name' => 'Xyzal', 'manufacturer' => 'Sanofi', 'active' => 'Levocetirizine'],
                ['name' => 'Singulair', 'manufacturer' => 'Merck', 'active' => 'Montelukast'],
                ['name' => 'Nasacort', 'manufacturer' => 'Sanofi', 'active' => 'Triamcinolone'],
                ['name' => 'Flonase', 'manufacturer' => 'GSK', 'active' => 'Fluticasone'],
                ['name' => 'Astelin', 'manufacturer' => 'Meda Pharmaceuticals', 'active' => 'Azelastine'],
                ['name' => 'Rhinocort', 'manufacturer' => 'AstraZeneca', 'active' => 'Budesonide'],
            ],
        ];

        foreach ($categories as $categoryName => $medicines) {
            $category = Category::where('name', $categoryName)->first();

            if (!$category) continue;

            // Shuffle for variety
            shuffle($medicines);

            // 10 valid
            foreach (array_slice($medicines, 0, 10) as $med) {
                Medicine::create($this->makeMedicine($category->id, $med, rand(20, 100), false));
            }

            // 3 out of stock
            foreach (array_slice($medicines, 0, 3) as $med) {
                Medicine::create($this->makeMedicine($category->id, $med, 0, false));
            }

            // 3 expired
            foreach (array_slice($medicines, 3, 3) as $med) {
                Medicine::create($this->makeMedicine($category->id, $med, rand(10, 50), true));
            }
        }
    }

    private function makeMedicine($categoryId, $data, $quantity, $expired)
    {
        $production = Carbon::now()->subMonths(rand(6, 18));
        $expiry = $expired
            ? Carbon::now()->subDays(rand(10, 90))
            : Carbon::now()->addMonths(rand(6, 24));

        return [
            'category_id' => $categoryId,
            'name' => $data['name'],
            'barcode' => $this->generateBarcode(),
            'manufacturer' => $data['manufacturer'],
            'active_ingredient' => $data['active'],
            'price' => rand(5, 50),
            'quantity' => $quantity,
            'production_date' => $production,
            'expiry_date' => $expiry,
            'img_url' => '/images/default_medicine.jpg',
        ];
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
