<?php

namespace App\Services\Category;

use App\Models\Category;

class CategoryService
{
    public function getAllCategories()
    {
        $category=Category::where('name', '!=', 'uncategorized')->get();
        return  $category;
    }

    public function createCategory(array $data)
    {
        return Category::create([
            'name' => $data['name'],
            'img_url' => $data['img_url'] ?? '/images/categories/default_category.png',
        ]);
    }

    public function updateCategory(Category $category, array $data)
    {   
        $category->update($data);

        return $category;
    }

    public function deleteCategory(Category $category) : void {
           $uncategorized = Category::where('name', 'uncategorized')->first();

        if (!$uncategorized) {
            throw new \Exception('Uncategorized category not found');
        }

        $category->medicines()->update(['category_id' => $uncategorized->id]);

        $category->delete();
    }
}
