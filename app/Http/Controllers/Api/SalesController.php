<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreSaleRequest;
use App\Http\Resources\SaleItemResource;
use App\Http\Resources\SaleResource;
use App\Services\Sales\SalesService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SalesController extends Controller
{
    protected $salesService;

    public function __construct(SalesService $salesService)
    {
        $this->salesService = $salesService;
    }

    public function index()
    {
        return SaleResource::collection($this->salesService->getAllSales());
    }

    public function store(StoreSaleRequest $request)
    {
        try {
            $result = $this->salesService->createSale(
                $request->validated()['items'],
                Auth::user()
            );

            return response()->json([
                'message' => 'Sale recorded successfully.',
                'invoice_number' => $result['invoice_number'],
                'total_price' => $result['total_price']
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        }
    }

    public function show($sale_id)
    {
        $items = $this->salesService->getSaleItems($sale_id);

        if (!$items) {
            return response()->json(["message" => "Sale not found."], 404);
        }

        return SaleItemResource::collection($items);
    }

    // public function destroy($sale_id)
    // {
    //     $sale = Sales::find($sale_id);

    //     if (!$sale) {
    //         return response()->json(["message" => "Sale not found."], 404);
    //     }

    //     $saleItems = $sale->sale_items;

    //     foreach ($saleItems as $saleItem) {
    //         $saleItem->medicine->increment('quantity', $saleItem->quantity);
    //     }

    //     $sale->delete();

    //     return response()->json(['message' => 'Sale deleted successfully']);
    // }
}
