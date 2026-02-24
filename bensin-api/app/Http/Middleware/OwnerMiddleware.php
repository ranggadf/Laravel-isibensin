<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OwnerMiddleware
{
 public function handle(Request $request, Closure $next)
{
    $user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Misal:
    // role 1 = customer
    // role 2 = owner
    // role 3 = admin

    if (!in_array($user->role, [2, 3])) {
        return response()->json(['message' => 'Akses hanya untuk Owner atau Admin'], 403);
    }

    return $next($request);
}
}