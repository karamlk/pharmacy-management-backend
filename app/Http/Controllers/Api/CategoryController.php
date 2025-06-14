<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('name', '!=', 'uncategorized')->get();

        return CategoryResource::collection($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'img_url' => ['nullable', 'string'],
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'img_url' => $validated['img_url'] ?? '/images/categories/default_category.png',
        ]);

        return new CategoryResource($category);
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->ignore($category->id)
            ]
        ]);

        $category->update($validated);

        return new CategoryResource($category);
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        $uncategorized = Category::where('name', 'uncategorized')->first();

        if (!$uncategorized) {
            return response()->json(['error' => 'Uncategorized category not found'], 500);
        }

        $category->medicines()->update(['category_id' => $uncategorized->id]);

        $category->delete();

        return response()->json(['message' => 'Category deleted and medicines reassigned'], 200);
    }
}
