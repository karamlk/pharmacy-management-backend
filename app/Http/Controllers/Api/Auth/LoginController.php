<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Models\UserSession;
use App\Models\UserSessionPair;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'the provided credentials are incorrect',
            ], 422);
        }

        if ($user->roles->contains('name', 'pharmacist')) {
            $loginSession = UserSession::create([
                'user_id' => $user->id,
                'type' => 'login',
                'occurred_at' => now()
            ]);

            UserSessionPair::create([
                'user_id' => $user->id,
                'login_session_id' => $loginSession->id
            ]);
        }

        $device = substr($request->userAgent() ?? 'Unknown_device', 0, 255);

        $role = $user->roles->pluck('name')->first();

        return response()->json([
            'access_token' => $user->createToken($device)->plainTextToken,
            'role' => $role,
        ], 200);
    }
}
