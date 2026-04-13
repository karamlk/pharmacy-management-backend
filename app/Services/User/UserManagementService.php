<?php

namespace App\Services\User;

use App\Exceptions\NotPharmacistException;
use App\Exceptions\PharmacistNotFoundException;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
    public function getPharmacists()
    {
        return User::whereHas('roles', function ($q) {
            $q->where('name', 'pharmacist');
        })->with(['sessionPairs.login', 'sessionPairs.logout'])->get();
    }

    public function getPharmacistById($id)
    {
        $user = User::with(['sessionPairs.login', 'sessionPairs.logout'])->find($id);

        if (! $user) {
            throw new PharmacistNotFoundException();
        }

        return $user;
    }

    public function createPharmacist(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'hourly_rate' => $data['hourly_rate'],
        ]);

        $role = Role::where('name', 'pharmacist')->first();
        $user->roles()->attach($role->id);

        return $user;
    }

    public function updatePharmacist($id, array $data)
    {
        $user = User::find($id);

        if (!$user) {
            throw new PharmacistNotFoundException();
        }

        if (!$user->roles->contains('name', 'pharmacist')) {
            throw new NotPharmacistException();
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return $user;
    }

    public function deletePharmacist($id)
    {
        $user = User::find($id);

        if (!$user) {
            throw new PharmacistNotFoundException();
        }

        if (!$user->roles->contains('name', 'pharmacist')) {
            throw new NotPharmacistException();
        }

        $user->delete();

        return true;
    }
}
