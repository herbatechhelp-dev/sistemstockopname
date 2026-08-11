<x-layouts.app title="Audit Logs" header="Audit Log Global">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
        <form method="GET" class="space-y-3">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari aksi/model..." class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <select name="user_id" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Semua User</option>
                    @foreach($users as $u)<option value="{{ $u->id }}" {{ request('user_id')==$u->id?'selected':'' }}>{{ $u->full_name ?? $u->name }}</option>@endforeach
                </select>
                <select name="action" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $a)<option value="{{ $a }}" {{ request('action')==$a?'selected':'' }}>{{ $a }}</option>@endforeach
                </select>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('superadmin.audit-logs.index') }}" class="px-4 py-2 text-gray-500 text-sm hover:text-gray-700">Reset</a>
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-900">Filter</button>
            </div>
        </form>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Waktu</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">User</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Aksi</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Model</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">IP</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                    <td class="px-4 py-3 text-gray-900">{{ $log->user->full_name ?? $log->user->name ?? 'System' }}</td>
                    <td class="px-4 py-3"><span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs">{{ $log->action }}</span></td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ class_basename($log->model_type) }} #{{ $log->model_id }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs font-mono">{{ $log->ip_address }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('superadmin.audit-logs.show', $log) }}" class="text-blue-600 hover:text-blue-800 text-sm">Lihat</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada audit log.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
</x-layouts.app>
