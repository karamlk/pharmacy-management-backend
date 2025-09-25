<?php

namespace Tests\Traits;

use App\Models\User;
use App\Models\Role;

trait WithAuthUser
{
    protected function actingAsAdmin()
    {
        $admin = User::factory()->create();

        $role = Role::firstOrCreate(['name' => 'admin']);

        $admin->roles()->syncWithoutDetaching([$role->id]);

        $this->actingAs($admin, 'sanctum');

        return $admin;
    }

    protected function actingAsPharmacist()
    {
        $pharmacist = User::factory()->create();

        $role = Role::firstOrCreate(['name' => 'pharmacist']);

        $pharmacist->roles()->syncWithoutDetaching([$role->id]);

        $this->actingAs($pharmacist, 'sanctum');

        return $pharmacist;
    }
}
