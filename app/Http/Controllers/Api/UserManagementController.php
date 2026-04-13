<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\PharmacistResource;
use App\Services\User\UserManagementService;

class UserManagementController extends Controller
{

    protected $userService;

    public function __construct(UserManagementService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $pharmacists = $this->userService->getPharmacists();

        return PharmacistResource::collection($pharmacists);
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->createPharmacist($request->validated());

        return response()->json(['message' => 'Pharmacist created successfully', 'data' => new PharmacistResource($user)], 201);
    }

    public function show($id)
    {
        $user = $this->userService->getPharmacistById($id);

        return new PharmacistResource($user);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = $this->userService->updatePharmacist($id, $request->validated());

        return new PharmacistResource($user);
    }

    public function destroy($id)
    {
        $this->userService->deletePharmacist($id);

        return response()->json(['message' => 'Pharmacist deleted successfully']);
    }
}
