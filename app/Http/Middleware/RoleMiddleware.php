<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,string $role): Response
    {
        $user = auth('sanctum')->user();

        // If user is not authenticated
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    
        // If user doesn't have the required role
        if (!$user->roles->contains('name', $role)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        return $next($request);
    }
}
