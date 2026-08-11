<x-layouts.app title="Monitoring Tim" header="Monitoring Progress SO">
    @if(!$session)
    <div class="text-center py-16">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        <h2 class="text-lg font-semibold text-gray-600">Belum Ada Sesi SO</h2>
        <p class="text-sm text-gray-400 mt-1">Monitoring tersedia setelah sesi SO dibuat dan dimulai.</p>
    </div>
    @else
    {{-- Session Info --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold text-gray-800">{{ $session->name }}</h3>
            <p class="text-sm text-gray-500">
                Status:
                <span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $session->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                    {{ ucfirst($session->status) }}
                </span>
                @if($session->started_at)
                | Dimulai: {{ $session->started_at->format('d M Y H:i') }}
                @endif
            </p>
        </div>
        <button onclick="location.reload()" class="px-3 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 flex items-center space-x-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span>Refresh</span>
        </button>
    </div>

    {{-- Overall Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-8">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Tim</p>
            <p class="text-2xl font-bold text-gray-800">{{ $overall['total_teams'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Lokasi</p>
            <p class="text-2xl font-bold text-gray-800">{{ $overall['total_locations'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Lokasi Selesai</p>
            <p class="text-2xl font-bold text-green-600">{{ $overall['locations_done'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Progress</p>
            <p class="text-2xl font-bold text-blue-600">{{ $overall['progress'] }}%</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Entri</p>
            <p class="text-2xl font-bold text-gray-800">{{ $overall['total_entries'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $overall['pending'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Verified</p>
            <p class="text-2xl font-bold text-green-600">{{ $overall['verified'] }}</p>
        </div>
    </div>

    {{-- Overall Progress Bar --}}
    <div class="mb-8 bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex justify-between text-sm mb-2">
            <span class="font-medium text-gray-700">Progress Keseluruhan (Semua Lokasi Gudang)</span>
            <span class="text-gray-500">{{ $overall['locations_done'] }} / {{ $overall['total_locations'] }} lokasi</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4">
            <div class="bg-blue-600 h-4 rounded-full transition-all" style="width: {{ min($overall['progress'], 100) }}%"></div>
        </div>
    </div>

    {{-- Per-Team Cards --}}
    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-4">Detail Per Tim</h3>
    <div class="space-y-4">
        @forelse($teams as $data)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            {{-- Team Header --}}
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-blue-700 font-bold text-sm">{{ substr($data['team']->name, 0, 2) }}</span>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800">{{ $data['team']->name }}</h4>
                        <p class="text-xs text-gray-500">
                            TL: {{ $data['team']->leader->full_name ?? $data['team']->leader->name ?? '-' }}
                            | {{ $data['team']->members->count() }} petugas
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold {{ $data['progress'] >= 100 ? 'text-green-600' : ($data['progress'] > 0 ? 'text-blue-600' : 'text-gray-400') }}">
                        {{ $data['progress'] }}%
                    </span>
                    <p class="text-xs text-gray-400">{{ $data['locations_done'] }}/{{ $data['total_locations'] }} lokasi</p>
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="px-5 py-2">
                <div class="w-full bg-gray-100 rounded-full h-2.5">
                    <div class="h-2.5 rounded-full transition-all {{ $data['progress'] >= 100 ? 'bg-green-500' : 'bg-blue-500' }}" style="width: {{ min($data['progress'], 100) }}%"></div>
                </div>
            </div>

            {{-- Team Stats --}}
            <div class="px-5 py-3 grid grid-cols-4 gap-3 border-t border-gray-50">
                <div>
                    <p class="text-xs text-gray-400">Total Entri</p>
                    <p class="text-lg font-bold text-gray-800">{{ $data['total_entries'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Pending</p>
                    <p class="text-lg font-bold text-yellow-600">{{ $data['pending'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Verified</p>
                    <p class="text-lg font-bold text-green-600">{{ $data['verified'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Terakhir Input</p>
                    <p class="text-sm font-medium text-gray-600">
                        {{ $data['last_entry_at'] ? $data['last_entry_at']->diffForHumans() : '-' }}
                    </p>
                </div>
            </div>

            {{-- Per-Petugas Breakdown --}}
            @if($data['petugas_stats']->count() > 0)
            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                <p class="text-xs font-semibold text-gray-500 mb-2">Kontribusi Petugas</p>
                <div class="space-y-1">
                    @foreach($data['petugas_stats'] as $ps)
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-700">{{ $ps['name'] }}</span>
                        <div class="flex items-center space-x-3">
                            <span class="text-gray-500">{{ $ps['total'] }} entri</span>
                            @if($ps['pending'] > 0)
                            <span class="px-1.5 py-0.5 bg-yellow-100 text-yellow-700 rounded text-xs">{{ $ps['pending'] }} pending</span>
                            @endif
                            @if($ps['verified'] > 0)
                            <span class="px-1.5 py-0.5 bg-green-100 text-green-700 rounded text-xs">{{ $ps['verified'] }} verified</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Allocated Locations --}}
            <div class="px-5 py-3 border-t border-gray-100">
                <p class="text-xs font-semibold text-gray-500 mb-2">Lokasi Alokasi</p>
                <div class="flex flex-wrap gap-1">
                    @foreach($data['team']->locationAllocations as $alloc)
                    <span class="px-2 py-0.5 text-xs rounded-full border {{ in_array($alloc->location_id, \App\Models\SoEntry::where('team_id', $data['team']->id)->where('session_id', $session->id)->pluck('location_id')->toArray()) ? 'bg-green-50 border-green-200 text-green-700' : 'bg-gray-50 border-gray-200 text-gray-500' }}">
                        {{ $alloc->location->name ?? '-' }}
                    </span>
                    @endforeach
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
            <p class="text-gray-400">Belum ada tim di sesi ini.</p>
        </div>
        @endforelse
    </div>

    {{-- Auto-refresh every 30 seconds --}}
    @push('scripts')
    <script>
        // Auto-refresh every 30 seconds for live monitoring
        setTimeout(() => location.reload(), 30000);
    </script>
    @endpush
    @endif
</x-layouts.app>
