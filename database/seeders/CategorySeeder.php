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
        // $categories = ['Pain Relief', 'Antibiotics', 'Vitamins', 'Allergy'];

        Category::firstOrCreate(
            ['name' => 'Uncategorized'],
            ['img_url' => '/images/categories/default_category.png']
        );

        $categories = [
            ['name' => 'Pain Relief', 'img_url' => '/images/categories/pain_relief.jpg'],
            ['name' => 'Antibiotics', 'img_url' => '/images/categories/antibiotics.png'],
            ['name' => 'Vitamins', 'img_url' => '/images/categories/vitamins.jpg'],
            ['name' => 'Allergy', 'img_url' => '/images/categories/allergy.png'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                ['img_url' => $category['img_url']]
            );
        }
    }
}
