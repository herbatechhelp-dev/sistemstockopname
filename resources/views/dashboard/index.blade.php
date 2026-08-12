<x-layouts.app title="Dashboard Rekonsiliasi" header="Dashboard Analisis Selisih">
    @if(!$session)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 sm:p-12 text-center">
        <div class="w-20 h-20 mx-auto bg-blue-50 rounded-full flex items-center justify-center mb-4">
            <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <p class="text-gray-500 text-lg mb-4">Belum ada sesi Stock Opname yang aktif.</p>
        <a href="/admin/sessions/create" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-medium hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg shadow-blue-600/20">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Buat Sesi Baru
        </a>
    </div>
    @else
    {{-- Session Selector --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="min-w-[250px] flex-1">
                <label class="block text-xs text-gray-500 mb-1">Sesi SO</label>
                <select name="session_id" onchange="this.form.submit()" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @foreach($allSessions as $s)
                    <option value="{{ $s->id }}" {{ $session->id == $s->id ? 'selected' : '' }}>
                        {{ $s->name }} ({{ ucfirst($s->status) }})
                    </option>
                    @endforeach
                </select>
            </div>
            <a href="{{ route('sessions.index') }}" class="px-4 py-2.5 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition-colors">Kelola Sesi</a>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Progress</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_entries'] }} <span class="text-gray-400 text-base font-normal">/ {{ $stats['total_snapshots'] }}</span></p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2 mt-3">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-500" style="width: {{ min($stats['progress'], 100) }}%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-2">{{ $stats['progress'] }}% terhitung</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-green-600 uppercase tracking-wide">Match (Cocok)</p>
                    <p class="text-2xl font-bold text-green-700 mt-1">{{ $stats['match'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-xs text-green-500 mt-2">Tidak ada selisih</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-red-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-red-600 uppercase tracking-wide">Selisih Kurang</p>
                    <p class="text-2xl font-bold text-red-700 mt-1">{{ $stats['minus'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </div>
            </div>
            <p class="text-xs text-red-500 mt-2">Fisik &lt; Sistem</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-blue-600 uppercase tracking-wide">Selisih Lebih</p>
                    <p class="text-2xl font-bold text-blue-700 mt-1">{{ $stats['plus'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
            </div>
            <p class="text-xs text-blue-500 mt-2">Fisik &gt; Sistem</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Item atau lokasi..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="min-w-[150px]">
                <label class="block text-xs text-gray-500 mb-1">Kategori</label>
                <select name="category_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua</option>
                    @foreach($categories as $c)<option value="{{ $c->id }}" {{ request('category_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="min-w-[150px]">
                <label class="block text-xs text-gray-500 mb-1">Lokasi</label>
                <select name="location_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua</option>
                    @foreach($locations as $l)<option value="{{ $l->id }}" {{ request('location_id')==$l->id?'selected':'' }}>{{ $l->name }}</option>@endforeach
                </select>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900 transition-colors">
                Filter
            </button>
            <a href="/dashboard" class="px-4 py-2.5 text-gray-500 text-sm hover:text-gray-700 border border-gray-300 rounded-lg">Reset</a>
        </form>
    </div>

    {{-- Reconciliation Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 whitespace-nowrap">Item</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 whitespace-nowrap">Lokasi</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 whitespace-nowrap">Batch</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600 whitespace-nowrap">Fisik</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600 whitespace-nowrap">Sistem</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600 whitespace-nowrap">Selisih</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 whitespace-nowrap">Status</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 whitespace-nowrap hidden lg:table-cell">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($entries as $entry)
                    @php $variance = $entry->variance; $vStatus = $entry->variance_status; @endphp
                    <tr class="hover:bg-gray-50 transition-colors table-row-hover {{ $vStatus === 'match' ? '' : ($vStatus === 'tolerable' ? 'bg-yellow-50/50' : 'bg-red-50/50') }}">
                        <td class="px-4 py-3">
                            <span class="font-medium text-gray-900">{{ $entry->item->name }}</span>
                            <br><span class="text-xs text-gray-400 font-mono">{{ $entry->item->sku }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs whitespace-nowrap">{{ $entry->location->name }}</td>
                        <td class="px-4 py-3 text-gray-500 font-mono text-xs whitespace-nowrap">{{ $entry->batch_code ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ number_format($entry->fisik_qty, 0) }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ $variance !== null ? number_format($entry->getSnapshot()->system_qty, 0) : '-' }}</td>
                        <td class="px-4 py-3 text-right font-bold {{ $variance > 0 ? 'text-blue-600' : ($variance < 0 ? 'text-red-600' : 'text-green-600') }} whitespace-nowrap">
                            {{ $variance !== null ? ($variance > 0 ? '+' : '') . number_format($variance, 0) : '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2.5 py-1 text-xs rounded-full font-medium {{ $vStatus === 'match' ? 'bg-green-100 text-green-700' : ($vStatus === 'tolerable' ? 'bg-yellow-100 text-yellow-700' : ($vStatus === 'unacceptable' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600')) }}">
                                {{ $vStatus === 'match' ? 'Cocok' : ($vStatus === 'tolerable' ? 'Toleransi' : ($vStatus === 'unacceptable' ? 'Selisih' : ucfirst($entry->status))) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 hidden lg:table-cell">{{ $entry->petugas->full_name ?? $entry->petugas->name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $entries->links() }}
    @endif
</x-layouts.app>