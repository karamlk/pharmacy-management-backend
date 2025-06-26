<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PharmacistResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index()
    {
        $pharmacists = User::whereHas('roles', function ($q) {
            $q->where('name', 'pharmacist');
        })->with(['sessionPairs.login', 'sessionPairs.logout'])->get();

        return PharmacistResource::collection($pharmacists);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'hourly_rate' => 'required|numeric|min:0',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'hourly_rate' => $validated['hourly_rate'],
        ]);

        $role = Role::where('name', 'pharmacist')->first();
        $user->roles()->attach($role->id);

        return response()->json(['message' => 'Pharmacist created successfully', 'data' => $user], 201);
    }


    public function show($id)
    {
        $user = User::findOrFail($id);
        return new PharmacistResource($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (!$user->roles->contains('name', 'pharmacist')) {
            return response()->json(['error' => 'User is not a pharmacist'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'hourly_rate' => 'sometimes|numeric|min:0',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return new PharmacistResource($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (!$user->roles->contains('name', 'pharmacist')) {
            return response()->json(['error' => 'User is not a pharmacist'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Pharmacist deleted successfully']);
    }
}
