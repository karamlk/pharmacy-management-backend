<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserSessionPair>
 */
class UserSessionPairFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();

        $loginSession = UserSession::factory()->create([
            'user_id' => $user->id,
            'type' => 'login',
        ]);

        $logoutSession = UserSession::factory()->create([
            'user_id' => $user->id,
            'type' => 'logout',
        ]);

        return [
            'user_id' => $user->id,
            'login_session_id' => $loginSession->id,
            'logout_session_id' => $logoutSession->id,
        ];
    }
}
