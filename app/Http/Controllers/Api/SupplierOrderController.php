<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\StoreSupplierOrderRequest;
use App\Http\Resources\SupplierOrderItemResource;
use App\Http\Resources\SupplierOrderResource;
use App\Models\SupplierOrder;
use App\Services\Supplier\SupplierOrderService;
use Exception;

class SupplierOrderController extends Controller
{
    protected $service;

    public function __construct(SupplierOrderService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $orders = $this->service->getAllSupplierOrders();
        return SupplierOrderResource::collection($orders);
    }

    public function show($id)
    {
        try {
            $order = $this->service->getSupplierOrder($id);
            return SupplierOrderItemResource::collection($order->items);
        } catch (Exception $e) {
            return response()->json(['error' => 'Order not found.'], 404);
        }
    }

    public function store(StoreSupplierOrderRequest $request)
    {
        try {
            $result = $this->service->createFullOrder($request->validated());
            return response()->json([
                'message' => 'The order was created successfully.',
                'order_id' => $result['order_id'],
                'total_price' => $result['total_price']
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
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
