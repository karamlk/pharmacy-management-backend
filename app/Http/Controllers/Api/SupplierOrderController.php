<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SupplierOrderItemResource;
use App\Http\Resources\SupplierOrderResource;
use App\Models\Medicine;
use App\Models\Supplier;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SupplierOrderController extends Controller
{
    public function index()
    {
        $orders = SupplierOrder::with([
            'supplier' => fn($q) => $q->withTrashed(),
            'user' => fn($q) => $q->withTrashed()
        ])->latest()->get();

        return SupplierOrderResource::collection($orders);
    }

    public function show($id)
    {
        $order = SupplierOrder::with([
            'supplier' => fn($q) => $q->withTrashed(),
            'user' => fn($q) => $q->withTrashed(),
            'items.medicine' => fn($q) => $q->withTrashed()
        ])->find($id);

        if (! $order) {
            return response()->json(['error' => 'Order not found.'], 404);
        }

        return SupplierOrderItemResource::collection($order->items);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_name' => 'required|string|exists:suppliers,name',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed.',
                'details' => $validator->errors()
            ], 422);
        }

        $supplier = Supplier::where('name', $request->supplier_name)->first();

        $validatedItems = [];
        $totalPrice = 0;

        foreach ($request->items as $index => $item) {
            $medicine = Medicine::where('name', $item['name'])->first();

            if (! $medicine) {
                return response()->json([
                    'error' => "Medicine '{$item['name']}' not found. Please add it to the system first."
                ], 422);
            }

            $quantity = $item['quantity'];
            $unitPrice = $medicine->price;
            $subtotal = $unitPrice * $quantity;
            $totalPrice += $subtotal;

            $validatedItems[] = [
                'medicine_id' => $medicine->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice
            ];
        }

        $user = Auth::user();

        $order = SupplierOrder::create([
            'supplier_id' => $supplier->id,
            'user_id' => $user->id,
            'order_date' => now(),
            'total_price' => $totalPrice,
        ]);

        $supplier->increment('balance', $totalPrice);

        foreach ($validatedItems as $item) {
            $item['supplier_order_id'] = $order->id;
            SupplierOrderItem::create($item);
            Medicine::where('id', $item['medicine_id'])
                ->increment('quantity', $item['quantity']);
        }

        return response()->json([
            'message' => 'The order was created successfully.',
            'order_id' => $order->id,
            'total_price' => $totalPrice
        ], 201);
    }

    public function ordersBySupplier($supplierId)
    {
        $orders = SupplierOrder::with([
            'user' => fn($q) => $q->withTrashed(),
            'supplier' => fn($q) => $q->withTrashed()
        ])->where('supplier_id', $supplierId)->latest()->get();

        return SupplierOrderResource::collection($orders);
    }
}
