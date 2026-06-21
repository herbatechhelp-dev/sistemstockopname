<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
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
        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('superadmin.users.index', compact('users'));
    }

    public function create()
    {
        return view('superadmin.users.form', [
            'roles' => ['superadmin', 'admin', 'team_leader', 'petugas_so'],
        ]);
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

        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->has('is_active');
        $user = User::create($data);
        AuditLog::log('superadmin_create_user', User::class, $user->id);

        return redirect('/superadmin/users')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('superadmin.users.form', [
            'user' => $user,
            'roles' => ['superadmin', 'admin', 'team_leader', 'petugas_so'],
        ]);
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

        $oldValues = $user->toArray();
        if ($data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $data['is_active'] = $request->has('is_active');
        $user->update($data);
        AuditLog::log('superadmin_update_user', User::class, $user->id, $oldValues, $data);

        return redirect('/superadmin/users')->with('success', 'User berhasil diperbarui.');
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        AuditLog::log('superadmin_toggle_active', User::class, $user->id, ['is_active' => !$user->is_active], ['is_active' => $user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "User berhasil {$status}.");
    }

    public function resetPassword(User $user, Request $request)
    {
        $data = $request->validate(['password' => 'required|min:6']);
        $user->update(['password' => Hash::make($data['password'])]);
        AuditLog::log('superadmin_reset_password', User::class, $user->id);
        return back()->with('success', 'Password berhasil direset.');
    }
}
