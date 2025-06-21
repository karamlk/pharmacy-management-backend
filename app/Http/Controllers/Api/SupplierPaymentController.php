<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SupplierPaymentResource;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierPaymentController extends Controller
{
    public function index()
    {
       $supplierPayments = SupplierPayment::all();
        return SupplierPaymentResource::collection($supplierPayments);
    }

        public function show($supplier_id)
    {
        $supplier = Supplier::find($supplier_id);

        if (!$supplier) {
            return response()->json(['error' => 'Supplier not found'], 404);
        }

        $supplier_payments = $supplier->payments;
        
        return SupplierPaymentResource::collection($supplier_payments);
    }


    public function store(Request $request, $supplier_id)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

         $supplier = Supplier::find($supplier_id);

        if (!$supplier) {
            return response()->json(['error' => 'Supplier not found'], 404);
        }

        if ($validatedData['amount'] > $supplier->balance) {
        return response()->json([
            'error' => 'Payment amount exceeds supplier balance.'
        ], 422);
    }

        SupplierPayment::create([
            'supplier_id' => $supplier_id,
            'user_id' => $user->id,
            'amount' => $validatedData['amount'],
            'payment_date' => now(),
        ]);

        $supplier->decrement('balance', $validatedData['amount']);

        return response()->json(['message' => 'Supplier payment created successfully.'], 200);
    }

}
