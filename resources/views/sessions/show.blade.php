<x-layouts.app title="Detail Sesi" header="Sesi: {{ $session->name }}">
    <div class="mb-6 flex items-center space-x-4">
        <span class="px-3 py-1 text-sm rounded-full {{ $session->status === 'active' ? 'bg-green-100 text-green-700' : ($session->status === 'completed' ? 'bg-blue-100 text-blue-700' : ($session->status === 'closed' ? 'bg-gray-200 text-gray-700' : 'bg-yellow-100 text-yellow-700')) }}">{{ ucfirst($session->status) }}</span>
        <span class="text-sm text-gray-500">Dibuat: {{ $session->created_at->format('d M Y H:i') }}</span>
        @if($session->started_at)<span class="text-sm text-gray-500">Dimulai: {{ $session->started_at->format('d M Y H:i') }}</span>@endif
    </div>

    {{-- Session Actions --}}
    <div class="mb-6 flex space-x-3">
        @if($session->status === 'draft')
        <form method="POST" action="/admin/sessions/{{ $session->id }}/start" onsubmit="return confirm('Mulai sesi ini? Snapshot stok akan dibuat.')">@csrf
            <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Mulai Sesi</button>
        </form>
        @endif
        @if($session->status === 'active')
        <form method="POST" action="/admin/sessions/{{ $session->id }}/complete" onsubmit="return confirm('Selesaikan sesi?')">@csrf
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Selesaikan Sesi</button>
        </form>
        @endif
        @if($session->status === 'completed')
        <form method="POST" action="/admin/sessions/{{ $session->id }}/close" onsubmit="return confirm('Tutup permanen sesi ini?')">@csrf
            <button class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm font-medium hover:bg-gray-700">Tutup Sesi</button>
        </form>
        @endif
    </div>

    {{-- Teams Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Tim ({{ $session->teams->count() }})</h3>
            @if($session->status === 'draft')
            <button onclick="document.getElementById('add-team-modal').classList.remove('hidden')" class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg text-sm font-medium hover:bg-blue-200">+ Tambah Tim</button>
            @endif
        </div>
        @foreach($session->teams as $team)
        <div class="border border-gray-200 rounded-lg p-4 mb-4">
            <div class="flex justify-between items-center mb-3">
                <div>
                    <h4 class="font-semibold text-gray-800">{{ $team->name }}</h4>
                    <p class="text-xs text-gray-500">TL: {{ $team->leader->full_name ?? $team->leader->name }}</p>
                </div>
                @if($session->status === 'draft')
                <form method="POST" action="/admin/teams/{{ $team->id }}" onsubmit="return confirm('Hapus tim?')">@csrf @method('DELETE')
                    <button class="text-red-500 text-xs hover:text-red-700">Hapus Tim</button>
                </form>
                @endif
            </div>
            {{-- Members --}}
            <div class="mb-3">
                <p class="text-xs font-medium text-gray-500 mb-1">Anggota:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($team->members as $member)
                    <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                        {{ $member->user->full_name ?? $member->user->name }}
                        @if($session->status === 'draft')
                        <form method="POST" action="/admin/team-members/{{ $member->id }}" class="inline ml-1">@csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600">&times;</button>
                        </form>
                        @endif
                    </span>
                    @endforeach
                </div>
                @if($session->status === 'draft')
                <form method="POST" action="/admin/teams/{{ $team->id }}/members" class="mt-2 flex space-x-2">
                    @csrf
                    <select name="user_id" required class="px-3 py-1.5 border border-gray-300 rounded text-sm">
                        <option value="">Tambah Anggota...</option>
                        @foreach($availableUsers as $u)<option value="{{ $u->id }}">{{ $u->full_name ?? $u->name }}</option>@endforeach
                    </select>
                    <button class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded text-sm hover:bg-gray-200">Tambah</button>
                </form>
                @endif
            </div>
            {{-- Allocated Locations --}}
            <div>
                <p class="text-xs font-medium text-gray-500 mb-1">Lokasi Dialokasikan:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($team->locationAllocations as $alloc)
                    <span class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">
                        {{ $alloc->location->name }}
                        @if($session->status === 'draft')
                        <form method="POST" action="/admin/allocations/{{ $alloc->id }}" class="inline ml-1">@csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600">&times;</button>
                        </form>
                        @endif
                    </span>
                    @endforeach
                </div>
                @if($session->status === 'draft')
                <form method="POST" action="/admin/teams/{{ $team->id }}/locations" class="mt-2 flex space-x-2">
                    @csrf
                    <select name="location_id" required class="px-3 py-1.5 border border-gray-300 rounded text-sm">
                        <option value="">Alokasikan Lokasi...</option>
                        @foreach($locations as $loc)<option value="{{ $loc->id }}">{{ $loc->name }}</option>@endforeach
                    </select>
                    <button class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded text-sm hover:bg-gray-200">Alokasikan</button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Add Team Modal --}}
    @if($session->status === 'draft')
    <x-modal id="add-team-modal" title="Tambah Tim Baru">
        <form method="POST" action="/admin/sessions/{{ $session->id }}/teams" class="space-y-4">
            @csrf
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama Tim</label><input type="text" name="name" required placeholder="cth: Tim A" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Team Leader</label>
                <select name="team_leader_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @foreach($teamLeaders as $tl)<option value="{{ $tl->id }}">{{ $tl->full_name ?? $tl->name }}</option>@endforeach
                </select>
            </div>
            <button type="submit" class="w-full px-4 py-2 bg-blue-700 text-white rounded-lg font-medium hover:bg-blue-800">Tambah Tim</button>
        </form>
    </x-modal>
    @endif
</x-layouts.app>
