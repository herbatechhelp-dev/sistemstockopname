<x-layouts.mobile title="Verifikasi Data" sessionName="{{ $session->name ?? '' }}">
    @if(!$session)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <p class="text-gray-500">Belum ada sesi SO yang aktif.</p>
    </div>
    @elseif(!isset($team) || !$team)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
        <div class="w-16 h-16 mx-auto bg-yellow-50 rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
            </svg>
        </div>
        <p class="text-gray-500">Anda belum ditunjuk sebagai Team Leader pada sesi ini.</p>
    </div>
    @else
    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $entries->count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-yellow-200 p-4 text-center">
            <p class="text-xs text-yellow-600 uppercase tracking-wide">Pending</p>
            <p class="text-2xl font-bold text-yellow-700 mt-1">{{ $entries->where('status','pending')->count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-4 text-center">
            <p class="text-xs text-green-600 uppercase tracking-wide">Verified</p>
            <p class="text-2xl font-bold text-green-700 mt-1">{{ $entries->where('status','verified')->count() }}</p>
        </div>
    </div>

    {{-- Verify All --}}
    @if($entries->where('status','pending')->count() > 0)
    <form method="POST" action="{{ route('verification.verify-all') }}" class="mb-4" onsubmit="return confirm('Verifikasi semua data pending?')">
        @csrf
        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-green-600 to-green-700 text-white font-semibold rounded-xl shadow-lg shadow-green-600/20 hover:from-green-700 hover:to-green-800 active:scale-[0.98] transition-all flex items-center justify-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Verifikasi Semua ({{ $entries->where('status','pending')->count() }})
        </button>
    </form>
    @endif

    {{-- Entry List --}}
    <div class="space-y-3">
        @forelse($entries as $entry)
        <div class="bg-white rounded-xl border {{ $entry->status === 'pending' ? 'border-yellow-200' : 'border-green-200' }} shadow-sm p-4 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 text-sm truncate">{{ $entry->item->name }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-2">
                        <span class="font-mono bg-gray-100 px-1.5 py-0.5 rounded text-xs">{{ $entry->item->sku }}</span>
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            {{ $entry->location->name }}
                        </span>
                    </p>
                </div>
                <span class="px-2.5 py-1 text-xs rounded-full font-medium {{ $entry->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : ($entry->status === 'verified' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700') }}">
                    {{ ucfirst(str_replace('_',' ',$entry->status)) }}
                </span>
            </div>
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div>
                        <p class="text-xl font-bold text-gray-900">{{ number_format($entry->fisik_qty, 0) }}</p>
                        <p class="text-xs text-gray-500">{{ $entry->uom }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-xs text-gray-400">{{ $entry->petugas->full_name ?? $entry->petugas->name }}</span>
                    <a href="{{ route('verification.show', $entry) }}" class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg text-xs font-medium hover:bg-blue-200 transition-colors">
                        Detail
                    </a>
                </div>
            </div>
            @if($entry->batch_code)
            <div class="mt-2 pt-2 border-t border-gray-100">
                <p class="text-xs text-gray-400 flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Batch: {{ $entry->batch_code }}
                </p>
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
            <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-3">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <p class="text-gray-500">Belum ada data hitungan dari anggota tim.</p>
        </div>
        @endforelse
    </div>
    @endif
</x-layouts.mobile>