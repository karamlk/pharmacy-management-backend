<?php

namespace Tests\Traits;

use App\Models\User;
use App\Models\Role;

trait WithAuthUser
{

    protected function createAdmin()
    {
        $admin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'admin']);
        $admin->roles()->syncWithoutDetaching([$role->id]);
        return $admin;
    }

    protected function createPharmacist()
    {
        $pharmacist = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'pharmacist']);
        $pharmacist->roles()->syncWithoutDetaching([$role->id]);
        return $pharmacist;
    }

    protected function actingAsAdmin()
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'sanctum');

        return $admin;
    }

    protected function actingAsPharmacist()
    {
        $pharmacist = $this->createPharmacist();
        
        $this->actingAs($pharmacist, 'sanctum');

        return $pharmacist;
    }
}
