<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('user_id')) return redirect()->route('dashboard');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !password_verify($request->password, $user->password)) {
            return back()->withErrors(['auth' => 'Username atau password salah.'])->withInput(['username' => $request->username]);
        }

        session([
            'user_id'   => $user->id,
            'user_name' => $user->name,
            'user_type' => $user->type,
        ]);

        return redirect()->route('dashboard');
    }

    public function logout()
    {
        session()->flush();
        return redirect()->route('login');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password'     => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);

        $user = User::findOrFail(session('user_id'));

        if (!password_verify($request->old_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Password lama salah.']);
        }

        $user->update(['password' => bcrypt($request->new_password)]);

        return response()->json(['success' => true, 'message' => 'Password berhasil diubah.']);
    }
}
