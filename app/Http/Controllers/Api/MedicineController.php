<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Medicine\SearchMedicineRequest;
use App\Http\Requests\Medicine\StoreMedicineRequest;
use App\Http\Requests\Medicine\UpdateMedicineRequest;
use App\Http\Resources\MedicineResource;
use App\Services\Medicine\MedicineService;

class MedicineController extends Controller
{
    protected $medicineService;

    public function __construct(MedicineService $medicineService)
    {
        $this->medicineService = $medicineService;
    }

    public function index()
    {

        $medicines = $this->medicineService->getAllMedicines();

        return MedicineResource::collection($medicines);
    }

    public function getByCategory($categoryId)
    {
        $medicines = $this->medicineService->getMedicinesByCategory($categoryId);

        if (!$medicines) {
            return response()->json(
                ['error' => 'Category not found'],
                404
            );
        }

        return MedicineResource::collection($medicines);
    }

    public function show($id)
    {
        $data = $this->medicineService->getMedicineWithSimilar($id);

        if (!$data) {
            return response()->json(['error' => 'Medicine not found'], 404);
        }

        return response()->json([
            'medicine' => new MedicineResource($data['medicine']),
            'similar_medicines' => MedicineResource::collection($data['similar_medicines'])
        ]);
    }

    public function store(StoreMedicineRequest $request)
    {
        $data = $request->validated();

        $meds = isset($data[0]) ? $data : [$data];

        $savedMeds = $this->medicineService->createMedicines($meds);

        return response()->json([
            'message' => count($savedMeds) > 1
                ? 'Medications created successfully'
                : 'Medication created successfully',
            'data' => MedicineResource::collection($savedMeds)
        ], 201);
    }

    public function update(UpdateMedicineRequest $request, $id)
    {
        $medicine = $this->medicineService->updateMedicine($id, $request->validated());

        if (!$medicine) {
            return response()->json(['message' => 'Medicine not found'], 404);
        }

        return new MedicineResource($medicine);
    }

    public function destroy($id)
    {
        $medicine = $this->medicineService->deleteMedicine($id);

        if (!$medicine) {
            return response()->json(['message' => 'Medicine not found'], 404);
        }

        return response()->json(['message' => 'Medicine deleted successfully']);
    }

    public function search(SearchMedicineRequest $request)
    {
        $medicines = $this->medicineService->searchMedicines($request->validated());

        return response()->json([
            'message' => 'Success',
            'data' => MedicineResource::collection($medicines),
        ]);
    }

    public function expired()
    {
        $expiredMedicines = $this->medicineService->getExpiredMedicines();

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
        $outOfStock = $this->medicineService->getOutOfStockMedicines();

        return response()->json([
            'message' => 'Out-of-stock medicines retrieved successfully.',
            'data' => MedicineResource::collection($outOfStock)
        ]);
    }
}
