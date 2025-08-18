<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserSession;
use App\Models\UserSessionPair;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users with the 'pharmacist' role
        $pharmacists = User::whereHas('roles', function ($query) {
            $query->where('name', 'pharmacist');
        })->get();

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        foreach ($pharmacists as $pharmacist) {
            // Create around 15 work sessions for each pharmacist in the last month
            for ($i = 0; $i < 5; $i++) {
                // Generate a random login time within the last 30 days
                $loginTime = Carbon::createFromTimestamp(rand($startOfMonth->timestamp, $endOfMonth->timestamp))
                    ->setHour(rand(8, 10))
                    ->setMinute(rand(0, 59))
                    ->setSecond(0);
                // Generate a logout time 4 to 9 hours after login
                $logoutTime = $loginTime->copy()->addHours(rand(4, 9))->addMinutes(rand(0, 59));

                // 1. Create the login session record
                $loginSession = UserSession::create([
                    'user_id' => $pharmacist->id,
                    'type' => 'login',
                    'occurred_at' => $loginTime,
                ]);

                // 2. Create the logout session record
                $logoutSession = UserSession::create([
                    'user_id' => $pharmacist->id,
                    'type' => 'logout',
                    'occurred_at' => $logoutTime,
                ]);

                // 3. Create the pair to link them
                UserSessionPair::create([
                    'user_id' => $pharmacist->id,
                    'login_session_id' => $loginSession->id,
                    'logout_session_id' => $logoutSession->id,
                ]);
            }
        }
    }
}
