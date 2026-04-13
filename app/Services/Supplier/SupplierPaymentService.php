<?php

namespace App\Services\Supplier;

use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SupplierPaymentService
{
    public function getAllPayments()
    {
        return SupplierPayment::all();
    }

    public function getPaymentsBySupplier(Supplier $supplier)
    {
        return $supplier->payments;
    }

    public function createPayment(Supplier $supplier, float $amount): void
    {
        if ($amount > $supplier->balance) {
            throw ValidationException::withMessages([
                'amount' => ['Payment amount exceeds supplier balance.']
            ]);
        }

        SupplierPayment::create([
            'supplier_id' => $supplier->id,
            'user_id' => Auth::id(),
            'amount' => $amount,
            'payment_date' => now(),
        ]);

        $supplier->decrement('balance', $amount);
    }
}