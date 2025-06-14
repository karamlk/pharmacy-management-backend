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

        Category::firstOrCreate(
            ['name' => 'Uncategorized'],
            ['img_url' => '/images/default_category.png']
        );

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['name' => $name],
                ['img_url' => '/images/default_category.png']
            );
        }
    }
}
