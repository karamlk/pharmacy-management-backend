<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MedicineController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SalesController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\SupplierOrderController;
use App\Http\Controllers\Api\SupplierPaymentController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Api\UserSessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/medicines', [MedicineController::class, 'index']);
    Route::get('/medicines/expired', [MedicineController::class, 'expired']);
    Route::get('/medicines/outOfStock', [MedicineController::class, 'outOfStock']);

    Route::get('/suppliers', [SupplierController::class, 'index']);
    Route::get('/suppliers/{id}', [SupplierController::class, 'show']);
    Route::post('/suppliers', [SupplierController::class, 'store']);
    Route::put('suppliers/{id}', [SupplierController::class, 'update']);
    Route::delete('suppliers/{id}', [SupplierController::class, 'destroy']);

    Route::get('/supplier-orders/by-supplier/{id}', [SupplierOrderController::class, 'ordersBySupplier']);

    Route::get('/supplier-payments', [SupplierPaymentController::class, 'index']);
    Route::get('/suppliers/{id}/payments', [SupplierPaymentController::class, 'show']);
    Route::post('/suppliers/{id}/payments', [SupplierPaymentController::class, 'store']);

    Route::get('/user-sessions', [UserSessionController::class, 'index']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    Route::get('/pharmacists', [UserManagementController::class, 'index']);
    Route::post('/pharmacists', [UserManagementController::class, 'store']);
    Route::get('/pharmacists/{id}', [UserManagementController::class, 'show']);
    Route::put('/pharmacists/{id}', [UserManagementController::class, 'update']);
    Route::delete('/pharmacists/{id}', [UserManagementController::class, 'destroy']);

    Route::post('/logout', LogoutController::class)->middleware('auth:sanctum');
});

Route::middleware(['auth:sanctum', 'role:pharmacist'])->prefix('pharmacist')->group(function () {
    Route::get('/medicines', [MedicineController::class, 'index']);
    Route::get('/medicines/expired', [MedicineController::class, 'expired']);
    Route::get('/medicines/outOfStock', [MedicineController::class, 'outOfStock']);
    Route::get('/medicines/{id}', [MedicineController::class, 'show']);
    Route::post('/medicines', [MedicineController::class, 'store']);
    Route::put('/medicines/{id}', [MedicineController::class, 'update']);
    Route::delete('/medicines/{id}', [MedicineController::class, 'destroy']);
    Route::post('/medicines/search', [MedicineController::class, 'search']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{id}/medicines', [MedicineController::class, 'getByCategory']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    Route::get('/suppliers', [SupplierController::class, 'index']);
    Route::get('/suppliers/{id}', [SupplierController::class, 'show']);
    Route::get('/supplier-orders', [SupplierOrderController::class, 'index']);
    Route::post('/supplier-orders', [SupplierOrderController::class, 'store']);
    Route::get('/supplier-orders/{id}', [SupplierOrderController::class, 'show']);
    Route::post('/suppliers/{id}/payments', [SupplierPaymentController::class, 'store']);

    Route::get('/sales', [SalesController::class, 'index']);
    Route::get('/sales/{id}', [SalesController::class, 'show']);
    Route::post('/sales', [SalesController::class, 'store']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);


    Route::post('/logout', LogoutController::class)->middleware('auth:sanctum');
});

Route::post('/login', LoginController::class);
