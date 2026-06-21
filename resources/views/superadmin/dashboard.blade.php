<x-layouts.app title="System Dashboard" header="System Dashboard">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-500">Total Users</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_users'] }}</p>
            <p class="text-xs text-gray-400 mt-2">Admin: {{ $stats['total_admins'] }} | TL: {{ $stats['total_tl'] }} | Petugas: {{ $stats['total_petugas'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6">
            <p class="text-sm text-green-600">Active Sessions</p>
            <p class="text-3xl font-bold text-green-700 mt-1">{{ $stats['active_sessions'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-6">
            <p class="text-sm text-blue-600">Completed Sessions</p>
            <p class="text-3xl font-bold text-blue-700 mt-1">{{ $stats['completed_sessions'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-500">Total SO Entries</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_entries'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Sessions</h3>
            <div class="space-y-3">
                @forelse($recentSessions as $s)
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <div>
                        <p class="font-medium text-gray-700">{{ $s->name }}</p>
                        <p class="text-xs text-gray-500">by {{ $s->creator->full_name ?? $s->creator->name }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full {{ $s->status === 'active' ? 'bg-green-100 text-green-700' : ($s->status === 'completed' ? 'bg-blue-100 text-blue-700' : ($s->status === 'closed' ? 'bg-gray-100 text-gray-700' : 'bg-yellow-100 text-yellow-700')) }}">
                        {{ ucfirst($s->status) }}
                    </span>
                </div>
                @empty
                <p class="text-gray-400 text-sm">Belum ada sesi.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Activity</h3>
            <div class="space-y-3 max-h-64 overflow-y-auto">
                @forelse($recentLogs as $log)
                <div class="flex items-start space-x-3 py-2 border-b border-gray-100">
                    <div class="w-2 h-2 mt-2 bg-blue-400 rounded-full flex-shrink-0"></div>
                    <div>
                        <p class="text-sm text-gray-700">{{ $log->action }}</p>
                        <p class="text-xs text-gray-400">{{ $log->user->full_name ?? $log->user->name ?? 'System' }} - {{ $log->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <p class="text-gray-400 text-sm">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
