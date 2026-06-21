<x-layouts.app title="User Management" header="Manajemen User (Superadmin)">
    <div class="flex justify-between items-center mb-6">
        <form method="GET" class="flex space-x-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari user..." class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <select name="role" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua Role</option>
                <option value="superadmin" {{ request('role')=='superadmin'?'selected':'' }}>Superadmin</option>
                <option value="admin" {{ request('role')=='admin'?'selected':'' }}>Admin</option>
                <option value="team_leader" {{ request('role')=='team_leader'?'selected':'' }}>Team Leader</option>
                <option value="petugas_so" {{ request('role')=='petugas_so'?'selected':'' }}>Petugas SO</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm">Filter</button>
        </form>
        <a href="{{ route('superadmin.users.create') }}" class="px-4 py-2 bg-blue-700 text-white rounded-lg text-sm font-medium hover:bg-blue-800">+ Tambah User</a>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-6 py-3 font-medium text-gray-600">Nama</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-600">Email</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-600">Role</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-right px-6 py-3 font-medium text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 text-gray-900 font-medium">{{ $user->full_name ?? $user->name }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ $user->email }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-1 text-xs rounded-full capitalize {{ $user->role === 'superadmin' ? 'bg-red-100 text-red-700' : ($user->role === 'admin' ? 'bg-purple-100 text-purple-700' : ($user->role === 'team_leader' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700')) }}">
                            {{ str_replace('_',' ',$user->role) }}
                        </span>
                    </td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    <td class="px-6 py-3 text-right space-x-2">
                        <a href="{{ route('superadmin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-800 text-sm">Edit</a>
                        <form method="POST" action="{{ route('superadmin.users.toggle', $user) }}" class="inline">@csrf
                            <button class="{{ $user->is_active ? 'text-yellow-600 hover:text-yellow-800' : 'text-green-600 hover:text-green-800' }} text-sm">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                        <button onclick="document.getElementById('reset-pw-{{ $user->id }}').classList.toggle('hidden')" class="text-orange-600 hover:text-orange-800 text-sm">Reset PW</button>
                    </td>
                </tr>
                <tr id="reset-pw-{{ $user->id }}" class="hidden bg-yellow-50">
                    <td colspan="5" class="px-6 py-3">
                        <form method="POST" action="{{ route('superadmin.users.reset-password', $user) }}" class="flex items-center space-x-2">
                            @csrf
                            <input type="password" name="password" required minlength="6" placeholder="Password baru..." class="px-3 py-1.5 border border-gray-300 rounded text-sm">
                            <button class="px-3 py-1.5 bg-orange-600 text-white rounded text-sm hover:bg-orange-700">Reset</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada data user.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.app>
