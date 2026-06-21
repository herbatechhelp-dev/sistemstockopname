<x-layouts.app title="{{ isset($user) ? 'Edit' : 'Tambah' }} User" header="{{ isset($user) ? 'Edit' : 'Tambah' }} User">
    <div class="max-w-2xl">
        <form method="POST" action="{{ isset($user) ? route('superadmin.users.update', $user) : route('superadmin.users.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            @csrf
            @if(isset($user)) @method('PUT') @endif
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Username</label><input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg"></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Password {{ isset($user) ? '(kosongkan jika tidak diubah)' : '' }}</label><input type="password" name="password" {{ isset($user) ? '' : 'required' }} class="w-full px-4 py-2 border border-gray-300 rounded-lg"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        @foreach($roles as $r)<option value="{{ $r }}" {{ old('role', $user->role ?? '') == $r ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label><input type="text" name="full_name" value="{{ old('full_name', $user->full_name ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label><input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></div>
            </div>
            <div class="flex items-center"><input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }} class="mr-2 rounded"><label for="is_active" class="text-sm text-gray-700">Aktif</label></div>
            <div class="flex space-x-3">
                <button type="submit" class="px-6 py-2 bg-blue-700 text-white rounded-lg font-medium hover:bg-blue-800">Simpan</button>
                <a href="{{ route('superadmin.users.index') }}" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200">Batal</a>
            </div>
        </form>
    </div>
</x-layouts.app>
