<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MedicineResource;
use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MedicineController extends Controller
{
    public function index()
    {

        $medicines = Medicine::available()->with('category')->get()->map(function ($medicine) {
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


        $medicines = $category->medicines()->available()->get();

        return MedicineResource::collection($medicines);
    }

    public function show($id)
    {
        $medicine = Medicine::with('category')
            ->find($id);

        if (!$medicine) {
            return response()->json(['error' => 'Medicine not found'], 404);
        }

        $similarMeds = Medicine::where('active_ingredient', $medicine->active_ingredient)
            ->where('id', '!=', $medicine->id)
            ->get();

        return response()->json([
            'medicine' => new MedicineResource($medicine),
            'similar_medicines' => MedicineResource::collection($similarMeds)
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $meds = isset($data[0]) ? $data : [$data];

        $savedMeds = [];

        foreach ($meds as $med) {
            $validator = Validator::make($med, [
                'name' => ['required', 'string', 'max:255'],
                'barcode' => ['required', 'string', 'unique:medicines,barcode'],
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
                'barcode' => $med['barcode'],
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
            'barcode' => ['sometimes', 'string', Rule::unique('medicines')->ignore($medicine->id)],
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

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'expiry_from' => 'nullable|date',
            'expiry_to' => 'nullable|date|after_or_equal:expiry_from',
            'min_quantity' => 'nullable|integer|min:0',
            'max_quantity' => 'nullable|integer|min:0',
        ]);

        $query = $request->input('query');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $expiryFrom = $request->input('expiry_from');
        $expiryTo = $request->input('expiry_to');
        $minQuantity = $request->input('min_quantity');
        $maxQuantity = $request->input('max_quantity');

        $medicinesQuery = Medicine::query();

        if ($query) {
            $medicinesQuery->where(function ($q) use ($query) {
                $q->where('barcode', $query)
                    ->orWhere('name', 'LIKE', "%{$query}%")
                    ->orWhere('manufacturer', 'LIKE', "%{$query}%")
                    ->orWhere('active_ingredient', 'LIKE', "%{$query}%");
            });
        }

        if ($minPrice) {
            $medicinesQuery->where('price', '>=', $minPrice);
        }
        if ($maxPrice) {
            $medicinesQuery->where('price', '<=', $maxPrice);
        }

        if ($expiryFrom) {
            $medicinesQuery->where('expiry_date', '>=', $expiryFrom);
        }
        if ($expiryTo) {
            $medicinesQuery->where('expiry_date', '<=', $expiryTo);
        }

        if ($minQuantity) {
            $medicinesQuery->where('quantity', '>=', $minQuantity);
        }

        if ($maxQuantity) {
            $medicinesQuery->where('quantity', '<=', $maxQuantity);
        }


        $medicines = $medicinesQuery->paginate(15);


        return response()->json([
            'message' => 'Success',
            'data' => MedicineResource::collection($medicines),
        ]);
    }

    public function expired()
    {
        $expiredMedicines = Medicine::with('category')->where('expiry_date', '<', now()->toDateString())->get();

        if ($expiredMedicines->isEmpty()) {
            return response()->json([
                'message' => 'No expired medicines found',
                'data' => []
            ]);
        }
        return response()->json([
            'message' => 'Expired medicines retrieved successfully',
            'data' => MedicineResource::collection($expiredMedicines)
        ]);
    }

    public function outOfStock()
    {
        $outOfStock = Medicine::with('category')->where('quantity', '<=', 0)->get();

        return response()->json([
            'message' => 'Out-of-stock medicines retrieved successfully.',
            'data' => MedicineResource::collection($outOfStock)
        ]);
    }
}
