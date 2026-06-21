<x-layouts.mobile title="Input Data SO" sessionName="{{ $session->name ?? '' }}">
    @if(!$session)
    {{-- No Active Session --}}
    <div class="text-center py-16 px-4">
        <div class="w-24 h-24 mx-auto bg-slate-100 rounded-3xl flex items-center justify-center mb-6 shadow-inner">
            <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-extrabold text-slate-800 mb-2">Belum Ada Sesi Aktif</h2>
        <p class="text-slate-500 text-sm max-w-xs mx-auto mb-8">
            Admin perlu membuat dan memulai sesi Stock Opname terlebih dahulu.
        </p>
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50/50 rounded-2xl p-5 text-left max-w-sm mx-auto border border-blue-100/80 shadow-sm">
            <p class="text-xs font-bold text-blue-700 mb-3 flex items-center">
                <svg class="w-4.5 h-4.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Langkah yang diperlukan:
            </p>
            <ol class="text-xs text-slate-600 space-y-2 list-decimal list-inside">
                <li>Admin membuat sesi SO baru</li>
                <li>Admin menambahkan tim & anggota</li>
                <li>Admin mengalokasikan lokasi ke tim</li>
                <li>Admin memulai sesi (status = Active)</li>
            </ol>
        </div>
    </div>

    @elseif(!isset($team) || !$team)
    {{-- Active Session but User Not in Team --}}
    <div class="text-center py-16 px-4">
        <div class="w-24 h-24 mx-auto bg-amber-50 rounded-3xl flex items-center justify-center mb-6 shadow-inner">
            <svg class="w-12 h-12 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-extrabold text-slate-800 mb-2">Belum Masuk Tim</h2>
        <p class="text-slate-500 text-sm max-w-xs mx-auto mb-6">
            Sesi "<span class="font-bold text-slate-700">{{ $session->name }}</span>" sudah aktif, tetapi Anda belum dimasukkan ke tim manapun.
        </p>
        <div class="mt-4 p-4 bg-amber-50/60 border border-amber-100 rounded-2xl max-w-sm mx-auto">
            <p class="text-xs text-amber-800 font-medium leading-relaxed">Hubungi Admin atau Team Leader Anda untuk ditambahkan ke anggota tim dan dialokasikan lokasi kerja.</p>
        </div>
    </div>

    @else
    {{-- Active Session + Has Team --}}
    <div class="mb-5 bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Tim Kerja</p>
                <p class="text-xl font-extrabold text-slate-800">{{ $team->name }}</p>
            </div>
            <span class="px-3 py-1.5 text-xs rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold flex items-center shadow-sm">
                <span class="relative flex h-2 w-2 mr-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                Aktif
            </span>
        </div>
    </div>

    {{-- Today's Stats --}}
    <div class="grid grid-cols-2 gap-4 mb-5">
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-4 text-white shadow-md shadow-blue-500/10">
            <div class="flex justify-between items-center mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider opacity-80">Total Entri Anda</span>
                <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <p class="text-3xl font-black">{{ $entries->count() }}</p>
        </div>
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-4 text-white shadow-md shadow-emerald-500/10">
            <div class="flex justify-between items-center mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider opacity-80">Terverifikasi</span>
                <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-3xl font-black">{{ $entries->where('status', 'verified')->count() }}</p>
        </div>
    </div>

    {{-- Input Button --}}
    <a href="/entry/create" class="flex items-center justify-center w-full py-4 bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600 text-white text-base font-extrabold rounded-2xl shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/40 active:scale-[0.98] transition-all mb-6">
        <svg class="w-5.5 h-5.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        INPUT DATA BARU
    </a>

    {{-- Recent Entries --}}
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Entri Terakhir</h3>
        <span class="text-xs text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full font-bold">{{ $entries->count() }} data</span>
    </div>
    
    <div class="space-y-4">
        @forelse($entries as $entry)
        <div class="bg-white rounded-2xl border-l-4 {{ $entry->status === 'verified' ? 'border-emerald-500' : ($entry->status === 'pending' ? 'border-amber-400' : 'border-blue-400') }} border-y border-r border-slate-200/60 shadow-sm p-4.5 hover:shadow-md transition-all duration-200">
            <div class="flex justify-between items-start mb-2.5">
                <div class="flex-1 min-w-0 pr-2">
                    <p class="font-extrabold text-slate-800 truncate text-sm leading-snug">{{ $entry->item->name }}</p>
                    <div class="text-xs text-slate-400 mt-1 flex flex-wrap items-center gap-1.5 font-medium">
                        <span class="font-mono bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded text-[10px] font-bold">{{ $entry->item->sku }}</span>
                        <span>•</span>
                        <span class="flex items-center text-slate-500 bg-slate-50 border border-slate-100 rounded px-1.5 py-0.5 text-[10px]">
                            <svg class="w-3 h-3 mr-0.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            {{ $entry->location->name }}
                        </span>
                    </div>
                </div>
                <span class="px-2.5 py-1 text-[10px] rounded-full font-bold tracking-wider uppercase flex-shrink-0 {{ $entry->status === 'verified' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($entry->status === 'pending' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-200') }}">
                    {{ $entry->status === 'verified' ? 'Verified' : ($entry->status === 'pending' ? 'Pending' : ucfirst($entry->status)) }}
                </span>
            </div>
            
            <div class="flex justify-between items-end pt-2 border-t border-slate-100">
                <div>
                    <div class="flex items-baseline space-x-1">
                        <p class="text-2xl font-black text-slate-900">{{ number_format($entry->fisik_qty, 0) }}</p>
                        <p class="text-xs font-bold text-slate-400">{{ $entry->uom }}</p>
                    </div>
                    @if($entry->batch_code)
                    <p class="text-[10px] text-slate-400 font-bold mt-0.5 flex items-center">
                        <svg class="w-3 h-3 mr-0.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Batch: <span class="text-slate-500 font-mono ml-0.5">{{ $entry->batch_code }}</span>
                    </p>
                    @endif
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-[10px] font-bold text-slate-400">{{ $entry->created_at->setTimezone('Asia/Jakarta')->format('H:i') }}</p>
                    <p class="text-[9px] font-semibold text-slate-400/80 mt-0.5">{{ $entry->created_at->setTimezone('Asia/Jakarta')->format('d M Y') }}</p>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-14 bg-white rounded-2xl border border-slate-200/50 shadow-sm">
            <div class="w-16 h-16 mx-auto bg-slate-50 rounded-2xl flex items-center justify-center mb-3 shadow-inner">
                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <p class="text-slate-500 text-sm font-semibold">Belum ada entri</p>
            <p class="text-slate-400 text-xs mt-1">Tekan tombol 'INPUT DATA BARU' untuk mengisi data.</p>
        </div>
        @endforelse
    </div>
    @endif
</x-layouts.mobile>