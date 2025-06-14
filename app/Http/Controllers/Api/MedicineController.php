<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MedicineResource;
use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MedicineController extends Controller
{
    public function index()
    {

        $medicines = Medicine::with('category')->get()->map(function ($medicine) {
            $medicine->img_url = asset($medicine->img_url);
            return $medicine;
        });

        return MedicineResource::collection($medicines);
    }

    public function getByCategory($categoryId)
    {
        $category = Category::find($categoryId);

        if (! $category) {
            return response()->json([
                'error' => 'Category not found'
            ], 404);
        }


        $medicines = $category->medicines()->get();

        return MedicineResource::collection($medicines);
    }

    public function show($id)
    {
        $medicine = Medicine::with('category')
            ->find($id);

        if (!$medicine) {
            return response()->json(['error' => 'Medicine not found'], 404);
        }

        return new MedicineResource($medicine);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $meds = isset($data[0]) ? $data : [$data];

        $savedMeds = [];

        foreach ($meds as $med) {
            $validator = Validator::make($med, [
                'name' => ['required', 'string', 'max:255'],
                'category_name' => ['required', 'string', 'exists:categories,name'],
                'manufacturer' => ['required', 'string', 'max:255'],
                'active_ingredient' => ['required', 'string', 'max:255'],
                'price' => ['required', 'numeric', 'min:0'],
                'quantity' => ['required', 'integer', 'min:0'],
                'production_date' => ['required', 'date', 'before:expiry_date'],
                'expiry_date' => ['required', 'date', 'after:production_date'],
                'img_url' => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed for one or more items',
                    'details' => $validator->errors(),
                ], 422);
            }

            $category = Category::where('name', $med['category_name'])->first();

            $savedMeds[] = Medicine::create([
                'name' => $med['name'],
                'category_id' => $category->id,
                'manufacturer' => $med['manufacturer'],
                'active_ingredient' => $med['active_ingredient'],
                'price' => $med['price'],
                'quantity' => $med['quantity'],
                'production_date' => $med['production_date'],
                'expiry_date' => $med['expiry_date'],
                'img_url' => '/images/default_medicine.jpg',
            ]);
        }

        return response()->json([
            'message' => count($savedMeds) > 1 ? 'Medications created successfully' : 'Medication created successfully',
            'data' => MedicineResource::collection($savedMeds)
        ], 201);
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
            'active_ingredient' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'quantity' => ['sometimes', 'integer', 'min:0'],
            'production_date' => ['sometimes', 'date', 'before:expiry_date'],
            'expiry_date' => ['sometimes', 'date', 'after:production_date'],
        ]);

        if (isset($validated['category_name'])) {
            $category = Category::where('name', $validated['category_name'])->first();
            $validated['category_id'] = $category->id;
            unset($validated['category_name']);
        }

        $newProductionDate = $validated['production_date'] ?? $medicine->production_date;
        $newExpiryDate = $validated['expiry_date'] ?? $medicine->expiry_date;

        if (strtotime($newProductionDate) >= strtotime($newExpiryDate)) {
            return response()->json([
                'message' => 'The production date must be before the expiry date.'
            ], 422);
        }


        $medicine->update($validated);

        return new MedicineResource($medicine);
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
}
