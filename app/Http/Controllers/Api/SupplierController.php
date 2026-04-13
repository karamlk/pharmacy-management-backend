<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        $supplier = Supplier::all();
        return SupplierResource::collection($supplier);
    }

    public function store(StoreSupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());

        return new SupplierResource($supplier);
    }

    public function update(UpdateSupplierRequest $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->validated());

        return new SupplierResource($supplier);
    }

    public function show($id)
    {
        $supplier = Supplier::findOrFail($id);
        
        return new SupplierResource($supplier);
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully'], 200);
    }
}
