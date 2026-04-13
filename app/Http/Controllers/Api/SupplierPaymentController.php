<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\StoreSupplierPaymentRequest;
use App\Http\Resources\SupplierPaymentResource;
use App\Models\Supplier;
use App\Services\Supplier\SupplierPaymentService;

class SupplierPaymentController extends Controller
{
    protected $service;

    public function __construct(SupplierPaymentService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $payments = $this->service->getAllPayments();
        return SupplierPaymentResource::collection($payments);
    }

    public function show($id)
    {
        $supplier = Supplier::findOrFail($id);

        $payments = $this->service->getPaymentsBySupplier($supplier);

        return SupplierPaymentResource::collection($payments);
    }

    public function store(StoreSupplierPaymentRequest $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $this->service->createPayment(
            $supplier,
            $request->amount
        );

        return response()->json([
            'message' => 'Supplier payment created successfully.'
        ], 201);
    }
}