<?php

namespace App\Services\Sales;

use App\Models\Medicine;
use App\Models\SaleItem;
use App\Models\Sales;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesService{

     public function getAllSales()
    {
        return Sales::all();
    }

    public function prepareSaleItems(array $items): array
    {
        $validatedItems = [];
        $totalPrice = 0;

        foreach ($items as $item) {
            $medicine = Medicine::where('name', $item['name'])->first();

            if (!$medicine) {
                throw ValidationException::withMessages([
                    'items' => ["Medicine '{$item['name']}' not found."]
                ]);
            }

            if ($medicine->quantity < $item['quantity']) {
                throw ValidationException::withMessages([
                    'items' => ["Insufficient quantity for '{$item['name']}'. Available: {$medicine->quantity}"]
                ]);
            }

            $quantity = $item['quantity'];
            $unitPrice = $medicine->price;
            $subtotal = $quantity * $unitPrice;

            $totalPrice += $subtotal;

            // Reduce stock
            $medicine->quantity -= $quantity;
            $medicine->save();

            $validatedItems[] = [
                'medicine_id' => $medicine->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice
            ];
        }

        return [
            'validated_items' => $validatedItems,
            'total_price' => $totalPrice
        ];
    }

     public function createSaleRecord($user, float $totalPrice)
    {
        return Sales::create([
            'user_id' => $user->id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'invoice_date' => now(),
            'total_price' => $totalPrice
        ]);
    }

    public function storeSaleItems(array $validatedItems, int $saleId): void
    {
        foreach ($validatedItems as $item) {
            $item['sale_id'] = $saleId;
            SaleItem::create($item);
        }
    }

    public function createSale(array $items, $user): array
    {
        return DB::transaction(function () use ($items, $user) {

            $prepared = $this->prepareSaleItems($items);

            $sale = $this->createSaleRecord(
                $user,
                $prepared['total_price']
            );

            $this->storeSaleItems(
                $prepared['validated_items'],
                $sale->id
            );

            return [
                'invoice_number' => $sale->invoice_number,
                'total_price' => $sale->total_price
            ];
        });
    }

    public function getSaleItems(int $saleId)
    {
        $sale = Sales::with([
            'user' => fn($q) => $q->withTrashed(),
            'items.medicine' => fn($q) => $q->withTrashed()
        ])->find($saleId);

        return $sale ? $sale->items : null;
    }

     private function generateInvoiceNumber(): string
    {
        $count = Sales::count() + 1;

        return 'INV-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}