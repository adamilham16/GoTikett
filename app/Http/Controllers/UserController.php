<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Client;

class UserController extends Controller
{
    private function currentUser(): User
    {
        return User::findOrFail(session('user_id'));
    }

    public function pageIndex()
    {
        $users    = User::with('approver')->orderBy('name')->get();
        $managers = $users->where('type', 'manager')->values();
        $clients  = Client::orderBy('nama')->get(['id', 'nama']);
        $curUser  = $this->currentUser();

        return view('users.index', compact('users', 'managers', 'clients', 'curUser'));
    }

    public function index()
    {
        $users = User::with('approver:id,name')->get(['id','username','name','type','role','dept','color','approver_id','is_active']);
        return response()->json($users->map(fn($u) => [
            'id'        => $u->id,
            'username'  => $u->username,
            'name'      => $u->name,
            'type'      => $u->type,
            'role'      => $u->role,
            'dept'      => $u->dept,
            'color'     => $u->color,
            'initials'  => $u->initials,
            'approver'  => $u->approver?->name,
            'is_active' => $u->is_active,
        ]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users,username',
            'name'     => 'required|string|max:100',
            'type'     => 'required|in:it,manager,user,it_manager',
            'role'     => 'required|string|max:50',
            'dept'     => 'required|string|max:50',
            'color'    => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'email'    => 'nullable|email|unique:users,email',
        ]);

        $colors = ['#f472b6','#60a5fa','#a78bfa','#fb923c','#34d399','#fbbf24','#e879f9','#38bdf8','#4ade80','#f87171'];
        $count  = User::count();
        $color  = $request->color ?? $colors[$count % count($colors)];

        $user = User::create([
            'username'    => $request->username,
            'name'        => $request->name,
            'password'    => bcrypt($request->password ?? \Illuminate\Support\Str::random(10)),
            'type'        => $request->type,
            'role'        => $request->role,
            'dept'        => $request->dept,
            'color'       => $color,
            'approver_id' => $request->approver_id ?: null,
            'email'       => $request->email ?: null,
        ]);

        return response()->json(['success' => true, 'user' => ['id' => $user->id, 'name' => $user->name]]);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'name'        => 'sometimes|string|max:100',
            'type'        => 'sometimes|in:it,manager,user,it_manager',
            'role'        => 'sometimes|string|max:50',
            'dept'        => 'sometimes|string|max:50',
            'color'       => 'nullable|string|max:20',
            'approver_id' => 'nullable|exists:users,id',
            'email'       => 'nullable|email|unique:users,email,' . $id,
        ]);

        $user = User::findOrFail($id);
        $user->update($request->only(['name', 'type', 'role', 'dept', 'color', 'approver_id', 'email']));
        return response()->json(['success' => true]);
    }

    public function toggleActive(int $id)
    {
        $cur = $this->currentUser();
        if ($cur->id === $id) {
            return response()->json(['success' => false, 'message' => 'Tidak bisa menonaktifkan akun sendiri.'], 422);
        }
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);
        return response()->json(['success' => true, 'is_active' => $user->is_active]);
    }

    public function resetPassword(Request $request, int $id)
    {
        $request->validate([
            'password' => 'required|min:8',
        ]);

        User::findOrFail($id)->update(['password' => bcrypt($request->password)]);
        return response()->json(['success' => true]);
    }

    public function destroy(int $id)
    {
        return response()->json(['success' => false, 'message' => 'Gunakan fitur nonaktifkan untuk menonaktifkan pengguna.'], 422);
    }
}
