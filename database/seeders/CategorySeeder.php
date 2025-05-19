<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['Pain Relief', 'Antibiotics', 'Vitamins', 'Allergy'];

        foreach ($categories as $name) {
            Category::create(['name' => $name]);
        }
    }
}
