<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    private function currentUser(): User
    {
        return User::findOrFail(session('user_id'));
    }

    public function index()
    {
        $users = User::with('approver')->get();
        return response()->json($users->map(fn($u) => [
            'id'       => $u->id,
            'username' => $u->username,
            'name'     => $u->name,
            'type'     => $u->type,
            'role'     => $u->role,
            'dept'     => $u->dept,
            'color'    => $u->color,
            'initials' => $u->initials,
            'approver' => $u->approver?->name,
        ]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users,username',
            'name'     => 'required|string|max:100',
            'type'     => 'required|in:it,manager,user',
            'role'     => 'required|string|max:50',
            'dept'     => 'required|string|max:50',
            'color'    => 'nullable|string|max:20',
        ]);

        $colors = ['#f472b6','#60a5fa','#a78bfa','#fb923c','#34d399','#fbbf24','#e879f9','#38bdf8','#4ade80','#f87171'];
        $count  = User::count();
        $color  = $request->color ?? $colors[$count % count($colors)];

        $user = User::create([
            'username'    => $request->username,
            'name'        => $request->name,
            'password'    => bcrypt($request->username . '123'), // default password: username123
            'type'        => $request->type,
            'role'        => $request->role,
            'dept'        => $request->dept,
            'color'       => $color,
            'approver_id' => $request->approver_id ?: null,
        ]);

        return response()->json(['success' => true, 'user' => ['id' => $user->id, 'name' => $user->name]]);
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->only(['name', 'type', 'role', 'dept', 'color', 'approver_id']));
        return response()->json(['success' => true]);
    }

    public function destroy(int $id)
    {
        $cur = $this->currentUser();
        if ($cur->id === $id) {
            return response()->json(['success' => false, 'message' => 'Tidak bisa hapus akun sendiri.']);
        }
        User::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
