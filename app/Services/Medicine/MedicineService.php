<?php

namespace App\Services\Medicine;

use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MedicineService
{

    public function getAllMedicines()
    {
        return Medicine::available()->with('category')->get()->map(function ($medicine) {
            $medicine->img_url = asset($medicine->img_url);
            return $medicine;
        });
    }


    public function getMedicinesByCategory($categoryId)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return null;
        }

        return $category->medicines()->available()->get();
    }

    public function getMedicineWithSimilar($id)
    {
        $medicine = Medicine::with('category')
            ->find($id);

        if (!$medicine) {
            return null;
        }

        $similarMeds = Medicine::where('active_ingredient', $medicine->active_ingredient)
            ->where('id', '!=', $medicine->id)
            ->get();

        return [
            'medicine' => $medicine,
            'similar_medicines' => $similarMeds
        ];
    }

    public function createMedicines(array $meds)
    {
        $savedMeds = [];

        foreach ($meds as $med) {
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

        return $savedMeds;
    }

    public function updateMedicine($id, array $validated)
    {
        $medicine = Medicine::find($id);

        if (!$medicine) {
            return null;
        }

        if (isset($validated['category_name'])) {
            $category = Category::where('name', $validated['category_name'])->first();
            $validated['category_id'] = $category->id;
            unset($validated['category_name']);
        }

        $newProductionDate = $validated['production_date'] ?? $medicine->production_date;
        $newExpiryDate = $validated['expiry_date'] ?? $medicine->expiry_date;

        if (strtotime($newProductionDate) >= strtotime($newExpiryDate)) {
            throw ValidationException::withMessages([
                'production_date' => ['Production date must be before expiry date']
            ]);
        }

        $medicine->update($validated);

        return $medicine;
    }

    public function deleteMedicine($id)
    {
        $medicine = Medicine::find($id);

        if (!$medicine) {
            return false;
        }

        $medicine->delete();

        return true;
    }

    public function searchMedicines(array $filters)
    {
        $query = Medicine::available()->with('category');

        if (!empty($filters['query'])) {
            $q = $filters['query'];

            $query->where(function ($sub) use ($q) {
                $sub->where('barcode', $q)
                    ->orWhere('name', 'LIKE', "%{$q}%")
                    ->orWhere('manufacturer', 'LIKE', "%{$q}%")
                    ->orWhere('active_ingredient', 'LIKE', "%{$q}%");
            });
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (isset($filters['expiry_from'])) {
            $query->where('expiry_date', '>=', $filters['expiry_from']);
        }

        if (isset($filters['expiry_to'])) {
            $query->where('expiry_date', '<=', $filters['expiry_to']);
        }

        if (isset($filters['min_quantity'])) {
            $query->where('quantity', '>=', $filters['min_quantity']);
        }

        if (isset($filters['max_quantity'])) {
            $query->where('quantity', '<=', $filters['max_quantity']);
        }
        return $query->paginate(15);
    }


    public function getExpiredMedicines()
    {
        return Medicine::with('category')
            ->where('expiry_date', '<', now()->toDateString())
            ->get();
    }


    public function getOutOfStockMedicines()
    {
        return Medicine::with('category')
            ->where('quantity', '<=', 0)
            ->get();
    }
}
