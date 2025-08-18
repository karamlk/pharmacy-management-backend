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
            'hourly_rate' => null
        ]);

        $user->roles()->attach($adminRole);

        $pharmacistUser = User::create([
            'name' => 'Pharmacist User',
            'email' => 'pharmacist@example.com',
            'password' => Hash::make('password'),
            'hourly_rate' => 25.50
        ]);

        $pharmacistUser->roles()->attach($pharmacistRole);

        $pharmacist2 = User::create([
            'name' => 'Pharmacist John',
            'email' => 'pharmacist.john@example.com',
            'password' => Hash::make('password'),
            'hourly_rate' => 28.00,
        ]);
        $pharmacist2->roles()->attach($pharmacistRole);

        $pharmacist3 = User::create([
            'name' => 'Pharmacist Emily',
            'email' => 'pharmacist.emily@example.com',
            'password' => Hash::make('password'),
            'hourly_rate' => 26.75,
        ]);
        $pharmacist3->roles()->attach($pharmacistRole);
    }
}
