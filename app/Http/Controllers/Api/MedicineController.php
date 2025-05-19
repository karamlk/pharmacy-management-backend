<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MedicineController extends Controller
{
    public function index()
    {
        $medicines = Medicine::with('category')->get();

        return response()->json($medicines);
    }

    public function show($id)
    {
        $medicine = Medicine::with('category')->find($id);

        if (! $medicine) {
            return response()->json(['message' => 'Medicine not found'], 404);
        }

        return response()->json($medicine);
    }

    public function store(Request $request)
    {

        $validate = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_name' => ['required', 'string', 'exists:categories,name'],
            'manufacturer' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'expiry_date' => ['required', 'date']
        ]);

        $category = Category::where('name', $validate['category_name'])->first();

        $medicine = Medicine::Create([
            'name' => $validate['name'],
            'category_id' => $category->id,
            'manufacturer' => $validate['manufacturer'],
            'price' => $validate['price'],
            'stock' => $validate['stock'],
            'expiry_date' => $validate['expiry_date']
        ]);

        return response()->json($medicine, 201);
    }

    public function update(Request $request, $id)
    {
        $medicine = Medicine::find($id);

        if (!$medicine) {
            return response()->json(['message' => 'Medicine not found'], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'category_name' => ['sometimes', 'string', 'exists:categories,name'],
            'manufacturer' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'expiry_date' => ['sometimes', 'date'],
        ]);

        if (isset($validated['category_name'])) {
            $category = Category::where('name', $validated['category_name'])->first();
            $validated['category_id'] = $category->id;
            unset($validated['category_name']);
        }

        $medicine->update($validated);

        return response()->json($medicine);
    }

    public function destroy($id)
    {
        $medicine = Medicine::find($id);

        if (!$medicine) {
            return response()->json(['message' => 'Medicine not found'], 404);
        }

        $medicine->delete();

        return response()->json(['message' => 'Medicine deleted successfully']);
    }

    public function pharmacistView()
    {
        $inventory = Medicine::all();

        return response()->json([
            'data' => $inventory->map(function ($medicine) {
                return [
                    'id' => $medicine->id,
                    'name' => $medicine->name,
                    'quantity' => $medicine->stock,
                    'expiry_date' => $medicine->expiry_date,
                ];
            })
        ]);
    }
    public function pharmacistViewMedicine($id)
    {
        $medicine = Medicine::find($id);

        return response()->json([
            'id' => $medicine->id,
            'name' => $medicine->name,
            'quantity' => $medicine->stock,
            'expiry_date' => $medicine->expiry_date,
        ]);
    }
}
