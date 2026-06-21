<x-layouts.app title="SO Sessions (Global)" header="Semua Sesi Stock Opname">
    <div class="flex justify-between items-center mb-6">
        <form method="GET" class="flex space-x-2">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status')=='draft'?'selected':'' }}>Draft</option>
                <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
                <option value="completed" {{ request('status')=='completed'?'selected':'' }}>Completed</option>
                <option value="closed" {{ request('status')=='closed'?'selected':'' }}>Closed</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm">Filter</button>
        </form>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-6 py-3 font-medium text-gray-600">Nama Sesi</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-600">Dibuat Oleh</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-center px-6 py-3 font-medium text-gray-600">Tim</th>
                    <th class="text-center px-6 py-3 font-medium text-gray-600">Entri</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-600">Tanggal</th>
                    <th class="text-right px-6 py-3 font-medium text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($sessions as $session)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $session->name }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ $session->creator->full_name ?? $session->creator->name }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $session->status === 'active' ? 'bg-green-100 text-green-700' : ($session->status === 'completed' ? 'bg-blue-100 text-blue-700' : ($session->status === 'closed' ? 'bg-gray-200 text-gray-700' : 'bg-yellow-100 text-yellow-700')) }}">
                            {{ ucfirst($session->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-center">{{ $session->teams_count }}</td>
                    <td class="px-6 py-3 text-center">{{ $session->entries_count }}</td>
                    <td class="px-6 py-3 text-gray-500 text-xs">{{ $session->created_at->format('d M Y') }}</td>
                    <td class="px-6 py-3 text-right space-x-2">
                        <a href="{{ route('superadmin.sessions.show', $session) }}" class="text-blue-600 hover:text-blue-800 text-sm">Detail</a>
                        @if(in_array($session->status, ['active', 'completed']))
                        <form method="POST" action="{{ route('superadmin.sessions.force-close', $session) }}" class="inline" onsubmit="return confirm('Tutup paksa sesi ini?')">@csrf
                            <button class="text-red-600 hover:text-red-800 text-sm">Tutup Paksa</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-8 text-center text-gray-400">Belum ada sesi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $sessions->links() }}</div>
</x-layouts.app>
