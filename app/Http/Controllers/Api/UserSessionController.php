<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSessionPair;
use Illuminate\Http\Request;

class UserSessionController extends Controller
{
    public function index()
    {
        $pharmacistSessions = UserSessionPair::with(['login', 'logout', 'user'])
            ->whereHas('user.roles', function ($query) {
                $query->where('name','pharmacist');
            })
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('user_id');

        $result = $pharmacistSessions->map(function ($sessions) {
            $user = $sessions->first()->user;

            return [
                'pharmacist_name' => $user->name,
                'sessions' => $sessions->map(function ($session) {
                    return [
                        'login_time' => optional($session->login)->occurred_at,
                        'logout_time' => optional($session->logout)->occurred_at
                    ];
                })->values()
            ];
            
        })->values();

        return response()->json($result);
    }
}
