<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\MedicineController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/medicines', [MedicineController::class, 'index']);
    Route::get('/medicines/{id}', [MedicineController::class, 'show']);
    Route::post('/medicines', [MedicineController::class, 'store']);
    Route::put('/medicines/{id}', [MedicineController::class, 'update']);
    Route::delete('/medicines/{id}', [MedicineController::class, 'destroy']);
});
Route::middleware(['auth:sanctum', 'role:pharmacist'])->prefix('pharmacist')->group(function () {
    Route::get('/medicines', [MedicineController::class, 'pharmacistView']);
    Route::get('/medicines/{id}', [MedicineController::class, 'pharmacistViewMedicine']);
});

Route::post('/login', LoginController::class);
