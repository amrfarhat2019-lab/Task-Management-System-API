<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
{
    $user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    
    if (count($roles) === 1 && strpos($roles[0], ',') !== false) {
        $roles = explode(',', $roles[0]);
    }

    if (!in_array($user->role, $roles)) {
        return response()->json([
            'message' => 'Forbidden: You do not have permission to access this resource'
        ], 403);
    }

    return $next($request);
}

}
