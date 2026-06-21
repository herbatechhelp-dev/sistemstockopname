<x-layouts.mobile title="Input Data SO" sessionName="{{ $session->name ?? '' }}">
    @if(!$session)
    {{-- No Active Session --}}
    <div class="text-center py-12">
        <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-700 mb-2">Belum Ada Sesi SO Aktif</h2>
        <p class="text-gray-500 text-sm max-w-xs mx-auto mb-6">
            Admin perlu membuat dan memulai sesi Stock Opname terlebih dahulu.
        </p>
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 text-left max-w-xs mx-auto border border-blue-100">
            <p class="text-xs font-semibold text-blue-700 mb-2 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Langkah yang diperlukan:
            </p>
            <ol class="text-xs text-gray-600 space-y-1.5 list-decimal list-inside">
                <li>Admin membuat sesi SO baru</li>
                <li>Admin menambahkan tim & anggota</li>
                <li>Admin mengalokasikan lokasi ke tim</li>
                <li>Admin memulai sesi (status = Active)</li>
            </ol>
        </div>
    </div>

    @elseif(!isset($team) || !$team)
    {{-- Active Session but User Not in Team --}}
    <div class="text-center py-12">
        <div class="w-20 h-20 mx-auto bg-yellow-50 rounded-full flex items-center justify-center mb-4">
            <svg class="w-10 h-10 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-700 mb-2">Belum Masuk Tim</h2>
        <p class="text-gray-500 text-sm max-w-xs mx-auto">
            Sesi "<strong class="text-gray-700">{{ $session->name }}</strong>" sudah aktif, tapi Anda belum dialokasikan ke tim manapun.
        </p>
        <div class="mt-4 p-4 bg-yellow-50 rounded-xl max-w-xs mx-auto">
            <p class="text-xs text-yellow-700">Hubungi Admin untuk ditambahkan ke tim dan dialokasikan lokasi.</p>
        </div>
    </div>

    @else
    {{-- Active Session + Has Team --}}
    <div class="mb-4 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-xs text-gray-500">Tim Anda</p>
                <p class="text-lg font-bold text-gray-800">{{ $team->name }}</p>
            </div>
            <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-700 font-semibold flex items-center">
                <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5"></span>
                Aktif
            </span>
        </div>
    </div>

    <a href="/entry/create" class="flex items-center justify-center w-full py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-lg font-bold rounded-xl shadow-lg shadow-blue-600/20 hover:from-blue-700 hover:to-blue-800 active:scale-[0.98] transition-all mb-6">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        INPUT DATA BARU
    </a>

    {{-- Recent Entries --}}
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold text-gray-600">Entri Terakhir</h3>
        <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">{{ $entries->count() }} data</span>
    </div>
    <div class="space-y-3">
        @forelse($entries as $entry)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 truncate">{{ $entry->item->name }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 flex items-center flex-wrap gap-2">
                        <span class="font-mono bg-gray-100 px-1.5 py-0.5 rounded">{{ $entry->item->sku }}</span>
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            {{ $entry->location->name }}
                        </span>
                    </p>
                </div>
                <span class="px-2.5 py-1 text-xs rounded-full font-medium {{ $entry->status === 'verified' ? 'bg-green-100 text-green-700' : ($entry->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700') }}">
                    {{ $entry->status === 'verified' ? 'Verified' : ($entry->status === 'pending' ? 'Pending' : ucfirst($entry->status)) }}
                </span>
            </div>
            <div class="mt-3 flex justify-between items-end">
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($entry->fisik_qty, 0) }}</p>
                    <p class="text-sm text-gray-500">{{ $entry->uom }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400">{{ $entry->created_at->setTimezone('Asia/Jakarta')->format('H:i') }}</p>
                    <p class="text-xs text-gray-400">{{ $entry->created_at->setTimezone('Asia/Jakarta')->format('d M') }}</p>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-12">
            <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-3">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <p class="text-gray-400 text-sm">Belum ada entri</p>
            <p class="text-gray-300 text-xs mt-1">Tekan tombol di atas untuk input data</p>
        </div>
        @endforelse
    </div>
    @endif
</x-layouts.mobile>