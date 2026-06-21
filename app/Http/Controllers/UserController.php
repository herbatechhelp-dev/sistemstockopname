<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
        }
        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        // Admin can only see TL and Petugas (not superadmin/admin)
        if (auth()->user()->role === 'admin') {
            $query->whereIn('role', ['team_leader', 'petugas_so']);
        }

        $users = $query->orderBy('name')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = auth()->user()->isSuperadmin()
            ? ['superadmin', 'admin', 'team_leader', 'petugas_so']
            : ['team_leader', 'petugas_so'];
        return view('admin.users.form', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:superadmin,admin,team_leader,petugas_so',
            'full_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        // Admin cannot create superadmin or admin
        if (auth()->user()->role === 'admin' && in_array($data['role'], ['superadmin', 'admin'])) {
            abort(403);
        }

        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->has('is_active');
        $user = User::create($data);
        AuditLog::log('create', User::class, $user->id, null, array_merge($data, ['password' => '***']));

        return redirect('/admin/users')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = auth()->user()->isSuperadmin()
            ? ['superadmin', 'admin', 'team_leader', 'petugas_so']
            : ['team_leader', 'petugas_so'];
        return view('admin.users.form', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'role' => 'required|in:superadmin,admin,team_leader,petugas_so',
            'full_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        // Admin cannot edit superadmin/admin
        if (auth()->user()->role === 'admin' && in_array($user->role, ['superadmin', 'admin'])) {
            abort(403);
        }
        if (auth()->user()->role === 'admin' && in_array($data['role'], ['superadmin', 'admin'])) {
            abort(403);
        }

        $oldValues = $user->toArray();
        if ($data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $data['is_active'] = $request->has('is_active');
        $user->update($data);
        AuditLog::log('update', User::class, $user->id, $oldValues, array_merge($data, ['password' => '***']));

        return redirect('/admin/users')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }
        AuditLog::log('delete', User::class, $user->id, $user->toArray());
        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }
}
