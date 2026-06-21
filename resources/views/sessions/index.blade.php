<x-layouts.app title="SO Sessions" header="Manajemen Sesi Stock Opname">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari sesi..." class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Cari</button>
        </form>
        <a href="/admin/sessions/create" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg text-sm font-medium hover:from-blue-700 hover:to-blue-800 transition-all shadow-sm">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Buat Sesi Baru
        </a>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium text-gray-600 whitespace-nowrap">Nama Sesi</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-600 whitespace-nowrap">Status</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-600 whitespace-nowrap">Tim</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-600 whitespace-nowrap">Entri</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-600 whitespace-nowrap hidden md:table-cell">Dibuat</th>
                        <th class="text-right px-6 py-3 font-medium text-gray-600 whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sessions as $s)
                    <tr class="hover:bg-gray-50 transition-colors table-row-hover">
                        <td class="px-6 py-4 text-gray-900 font-medium">{{ $s->name }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-xs rounded-full font-medium {{ $s->status === 'active' ? 'bg-green-100 text-green-700' : ($s->status === 'completed' ? 'bg-blue-100 text-blue-700' : ($s->status === 'closed' ? 'bg-gray-200 text-gray-700' : 'bg-yellow-100 text-yellow-700')) }}">
                                {{ ucfirst($s->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ $s->teams_count }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $s->entries_count }}</td>
                        <td class="px-6 py-4 text-gray-500 text-xs hidden md:table-cell">{{ $s->created_at->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="/admin/sessions/{{ $s->id }}" class="inline-flex items-center px-2.5 py-1 text-blue-600 hover:text-blue-800 text-sm transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p>Belum ada sesi.</p>
                        </div>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $sessions->links() }}
</x-layouts.app>