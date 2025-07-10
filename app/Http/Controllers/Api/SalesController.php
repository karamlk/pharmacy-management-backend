<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SaleItemResource;
use App\Http\Resources\SaleResource;
use App\Models\Medicine;
use App\Models\SaleItem;
use App\Models\Sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SalesController extends Controller
{
    public function index()
    {
        $sales = Sales::all();
        return SaleResource::collection($sales);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors()
            ]);
        }

        DB::beginTransaction();

        try {
            $validatedItems = [];
            $totalPrice = 0;

            foreach ($request->items as $item) {
                $medicine = Medicine::where('name', $item['name'])->first();

                if (! $medicine) {
                    throw new \Exception("Medicine '{$item['name']}' not found. Please add it to the system first.");
                }

                if ($medicine->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient quantity for medicine '{$item['name']}'. Available: {$medicine->quantity}");
                }

                $quantity = $item['quantity'];
                $unitPrice = $medicine->price;
                $subtotal = $quantity * $unitPrice;
                $totalPrice += $subtotal;

                $medicine->quantity -= $quantity;
                $medicine->save();

                $validatedItems[] = [
                    'medicine_id' => $medicine->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice
                ];
            }

            $user = Auth::user();

            $sale = Sales::create([
                'user_id' => $user->id,
                'invoice_number' => 'INV-' . str_pad(Sales::count() + 1, 5, '0', STR_PAD_LEFT),
                'invoice_date' => now(),
                'total_price' => $totalPrice
            ]);

            foreach ($validatedItems as $item) {
                $item['sale_id'] = $sale->id;
                SaleItem::create($item);
            }

            DB::commit();

            return response()->json([
                'message' => 'Sale recorded successfully.',
                'invoice_number' => $sale->invoice_number,
                'total_price' => $sale->total_price
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Transaction failed',
                'details' => $e->getMessage()
            ], 422);
        }
    }

    public function show($sale_id)
    {
        $sale = Sales::with([
            'user' => fn($q) => $q->withTrashed(),
            'items.medicine' => fn($q) => $q->withTrashed()
        ])->find($sale_id);

        if (!$sale) {
            return response()->json(["message" => "Sale not found."], 404);
        }

        return SaleItemResource::collection($sale->items);
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
