<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $supplier = Supplier::all();
        return SupplierResource::collection($supplier);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255|unique:suppliers,name',
            'phone' => 'nullable|string|min:7|max:15|unique:suppliers,phone',
            'email' => 'nullable|email|unique:suppliers,email',
            'address' => 'nullable|string',
            'balance' => 'required|numeric|min:0',

        ]);
        $supplier = Supplier::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'balance' => $validated['balance'],
        ]);
        return new SupplierResource($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }
        $validated = $request->validate([
            'name' => 'sometimes|string|min:3|max:255|unique:suppliers,name,' . $supplier->id,
            'phone' => 'nullable|string|min:7|max:15|unique:suppliers,phone,' . $supplier->id,
            'email' => 'nullable|email|unique:suppliers,email,' . $supplier->id,
            'address' => 'nullable|string',
            'balance' => 'sometimes|numeric|min:0',
        ]);

        $supplier->update($validated);
        return new SupplierResource($supplier);
    }

    public function show($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }
        return new SupplierResource($supplier);
    }

    public function destroy($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {

            return response()->json(['message' => 'Supplier not found'], 404);
        }
        $supplier->delete();
        return response()->json(['message' => 'Supplier deleted successfully'], 200);
    }
}
