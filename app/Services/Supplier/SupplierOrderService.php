<?php

namespace App\Services\Supplier;

use App\Models\Medicine;
use App\Models\Supplier;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierOrderService
{

    public function getAllSupplierOrders()
    {
        return SupplierOrder::with([
            'supplier' => fn($q) => $q->withTrashed(),
            'user' => fn($q) => $q->withTrashed()
        ])->latest()->get();
    }

    public function getSupplierOrder(int $id): SupplierOrder
    {
        $order = SupplierOrder::with([
            'supplier' => fn($q) => $q->withTrashed(),
            'user' => fn($q) => $q->withTrashed(),
            'items.medicine' => fn($q) => $q->withTrashed()
        ])->find($id);

        if (! $order) {
            throw new \Exception('Supplier order not found.');
        }

        return $order;
    }

    public function createFullOrder(array $data): array
    {
        return DB::transaction(function () use ($data) {

            $supplier = Supplier::where('name', $data['supplier_name'])->firstOrFail();

            $prepared = $this->prepareOrderItems($data['items']);

            $order = $this->createOrder($supplier->id, $prepared['total_price']);

            $this->storeOrderItems($prepared['items'], $order);

            $supplier->increment('balance', $prepared['total_price']);

            return [
                'order_id' => $order->id,
                'total_price' => $prepared['total_price']
            ];
        });
    }

    private function prepareOrderItems(array $items): array
    {
        $validatedItems = [];
        $totalPrice = 0;

        foreach ($items as $index => $item) {
            $medicine = Medicine::where('name', $item['name'])->first();

            if (! $medicine) {
                throw new \Exception("Medicine '{$item['name']}' not found.");
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

        return [
            'items' => $validatedItems,
            'total_price' => $totalPrice,
        ];
    }

    private function createOrder(int $id, float $totalPrice)
    {
        return  SupplierOrder::create([
            'supplier_id' => $id,
            'user_id' => Auth::id(),
            'order_date' => now(),
            'total_price' => $totalPrice
        ]);
    }

    private function storeOrderItems(array $items, SupplierOrder $order)
    {
        foreach ($items as $item) {
            $item['supplier_order_id'] = $order->id;

            SupplierOrderItem::create($item);

            Medicine::where('id', $item['medicine_id'])
                ->increment('quantity', $item['quantity']);
        }
    }

    public function getAllOrdersBySupplier(int $supplierId)
    {
        return SupplierOrder::with([
            'user' => fn($q) => $q->withTrashed(),
            'supplier' => fn($q) => $q->withTrashed()
        ])->where('supplier_id', $supplierId)->latest()->get();
    }
}
