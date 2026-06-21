<x-layouts.app title="Recount Management" header="Permintaan Recount">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Item</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Lokasi</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Petugas Asli</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Qty Asli</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Petugas Recount</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Tanggal</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($recounts as $recount)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <span class="font-medium text-gray-900">{{ $recount->entry->item->name }}</span><br>
                        <span class="text-xs text-gray-400">{{ $recount->entry->item->sku }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $recount->entry->location->name }}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $recount->entry->petugas->full_name ?? $recount->entry->petugas->name }}</td>
                    <td class="px-4 py-3 text-right font-semibold">{{ number_format($recount->entry->fisik_qty, 0) }}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $recount->assignedPetugas->full_name ?? $recount->assignedPetugas->name }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $recount->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                            {{ ucfirst($recount->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $recount->created_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3 text-right">
                        @if($recount->status === 'pending')
                        <form method="POST" action="{{ route('recounts.complete', $recount) }}" class="inline" onsubmit="return confirm('Tandai recount selesai?')">@csrf
                            <button class="text-green-600 hover:text-green-800 text-sm">Selesai</button>
                        </form>
                        @else
                        <span class="text-gray-400 text-xs">Selesai</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-6 py-8 text-center text-gray-400">Belum ada permintaan recount.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $recounts->links() }}</div>
</x-layouts.app>
