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
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        // Gunakan user yang sudah di-load oleh AuthSession, hindari query ulang
        $user = $request->attributes->get('auth_user') ?? User::find(session('user_id'));
        if (!$user || !in_array($user->type, $roles)) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
