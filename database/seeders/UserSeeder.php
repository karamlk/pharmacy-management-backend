<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $pharmacistRole = Role::where('name', 'pharmacist')->first();

        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $user->roles()->attach($adminRole);

        $pharmacistUser = User::create([
            'name' => 'Pharmacist User',
            'email' => 'pharmacist@example.com',
            'password' => Hash::make('password'),
        ]);

        $pharmacistUser->roles()->attach($pharmacistRole);

    }
}
