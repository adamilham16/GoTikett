<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Usage: role:it  | role:manager | role:it,manager
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (!$user || !in_array($user->type, $roles)) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
