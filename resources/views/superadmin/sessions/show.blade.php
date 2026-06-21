<x-layouts.app title="Detail Sesi" header="Sesi: {{ $session->name }}">
    <div class="mb-6 flex items-center space-x-4">
        <span class="px-3 py-1 text-sm rounded-full {{ $session->status === 'active' ? 'bg-green-100 text-green-700' : ($session->status === 'completed' ? 'bg-blue-100 text-blue-700' : ($session->status === 'closed' ? 'bg-gray-200 text-gray-700' : 'bg-yellow-100 text-yellow-700')) }}">{{ ucfirst($session->status) }}</span>
        <span class="text-sm text-gray-500">Dibuat oleh: {{ $session->creator->full_name ?? $session->creator->name }}</span>
        <span class="text-sm text-gray-500">{{ $session->created_at->format('d M Y H:i') }}</span>
        @if($session->started_at)<span class="text-sm text-gray-500">Dimulai: {{ $session->started_at->format('d M Y H:i') }}</span>@endif
        @if($session->ended_at)<span class="text-sm text-gray-500">Berakhir: {{ $session->ended_at->format('d M Y H:i') }}</span>@endif
    </div>

    @if(in_array($session->status, ['active', 'completed']))
    <div class="mb-6">
        <form method="POST" action="{{ route('superadmin.sessions.force-close', $session) }}" onsubmit="return confirm('Tutup paksa sesi ini? Tindakan ini tidak dapat dibatalkan.')">@csrf
            <button class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Tutup Paksa Sesi</button>
        </form>
    </div>
    @endif

    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Total Tim</p>
            <p class="text-2xl font-bold text-gray-900">{{ $session->teams->count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Total Entri</p>
            <p class="text-2xl font-bold text-gray-900">{{ $session->entries->count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Total Anggota</p>
            <p class="text-2xl font-bold text-gray-900">{{ $session->teams->sum(fn($t) => $t->members->count()) }}</p>
        </div>
    </div>

    {{-- Teams --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Tim & Alokasi</h3>
        @foreach($session->teams as $team)
        <div class="border border-gray-200 rounded-lg p-4 mb-4">
            <div class="mb-2">
                <h4 class="font-semibold text-gray-800">{{ $team->name }}</h4>
                <p class="text-xs text-gray-500">TL: {{ $team->leader->full_name ?? $team->leader->name }}</p>
            </div>
            <div class="mb-2">
                <p class="text-xs font-medium text-gray-500 mb-1">Anggota ({{ $team->members->count() }}):</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($team->members as $member)
                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">{{ $member->user->full_name ?? $member->user->name }}</span>
                    @endforeach
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 mb-1">Lokasi ({{ $team->locationAllocations->count() }}):</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($team->locationAllocations as $alloc)
                    <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">{{ $alloc->location->name }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <a href="{{ route('superadmin.sessions.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">&larr; Kembali ke daftar sesi</a>
</x-layouts.app>
