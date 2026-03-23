<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }
        $user = \App\Models\User::find(session('user_id'));
        if (!$user || !$user->is_active) {
            session()->flush();
            return redirect()->route('login')->withErrors(['auth' => 'Akun Anda telah dinonaktifkan.']);
        }
        // Simpan user di request agar middleware lain tidak perlu query ulang
        $request->attributes->set('auth_user', $user);
        return $next($request);
    }
}
