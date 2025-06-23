<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserSession;
use App\Models\UserSessionPair;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __invoke(Request  $request)
    {
        $user = $request->user();
        
        if ($user->roles->contains('name', 'pharmacist')) {
            $logoutSession = UserSession::create([
                'user_id' => $user->id,
                'type' => 'logout',
                'occurred_at' => now()
            ]);

            $lastPair = UserSessionPair::where('user_id', $user->id)
                ->whereNull('logout_session_id')
                ->orderBy('id', 'desc')
                ->first();

            if ($lastPair) {
                $lastPair->update(['logout_session_id' => $logoutSession->id]);
            }
        }

        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'LogOut Successfully']);
    }
}
