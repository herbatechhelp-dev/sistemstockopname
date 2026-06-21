<x-layouts.mobile title="Verifikasi Data" sessionName="{{ $session->name ?? '' }}">
    @if(!$session)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-8 text-center">
        <div class="w-16 h-16 mx-auto bg-slate-100 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <p class="text-slate-500 font-semibold text-sm">Belum ada sesi SO yang aktif.</p>
    </div>
    @elseif(!isset($team) || !$team)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-8 text-center">
        <div class="w-16 h-16 mx-auto bg-amber-50 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
            </svg>
        </div>
        <p class="text-slate-600 font-bold text-base mb-1">Bukan Team Leader</p>
        <p class="text-slate-400 text-xs leading-relaxed">Anda belum ditunjuk sebagai Team Leader pada sesi aktif ini.</p>
    </div>
    @else
    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-4 text-center">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total</p>
            <p class="text-2xl font-black text-slate-800 mt-1">{{ $entries->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-amber-200/60 p-4 text-center">
            <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Pending</p>
            <p class="text-2xl font-black text-amber-700 mt-1">{{ $entries->where('status','pending')->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-200/60 p-4 text-center">
            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Verified</p>
            <p class="text-2xl font-black text-emerald-700 mt-1">{{ $entries->where('status','verified')->count() }}</p>
        </div>
    </div>

    {{-- Interactive Search & Filters --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-4 mb-4">
        <div class="relative">
            <input type="text" id="filter-input" placeholder="Cari barang, SKU, lokasi, petugas..." 
                class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-4 focus:ring-blue-100 focus:border-blue-600 transition-all">
            <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>
    </div>

    {{-- Verify All --}}
    @if($entries->where('status','pending')->count() > 0)
    <form method="POST" action="{{ route('verification.verify-all') }}" class="mb-5" onsubmit="return confirm('Verifikasi semua data pending?')">
        @csrf
        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-extrabold rounded-2xl shadow-md shadow-emerald-600/25 hover:shadow-emerald-600/35 active:scale-[0.98] transition-all flex items-center justify-center">
            <svg class="w-5.5 h-5.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            VERIFIKASI SEMUA PENDING ({{ $entries->where('status','pending')->count() }})
        </button>
    </form>
    @endif

    {{-- Entry List --}}
    <div class="space-y-4" id="entry-list-container">
        @forelse($entries as $entry)
        <div class="entry-card bg-white rounded-2xl border-l-4 {{ $entry->status === 'pending' ? 'border-amber-400 border-y border-r border-slate-200/60 shadow-sm shadow-amber-400/5' : 'border-emerald-500 border-y border-r border-slate-200/60 shadow-sm shadow-emerald-500/5' }} p-4.5 hover:shadow-md transition-all duration-200"
            data-sku="{{ $entry->item->sku }}"
            data-name="{{ $entry->item->name }}"
            data-location="{{ $entry->location->name }}"
            data-petugas="{{ $entry->petugas->full_name ?? $entry->petugas->name }}">
            
            <div class="flex justify-between items-start mb-2.5">
                <div class="flex-1 min-w-0 pr-2">
                    <p class="font-extrabold text-slate-800 text-sm leading-snug truncate">{{ $entry->item->name }}</p>
                    <div class="text-[10px] text-slate-400 mt-1 flex flex-wrap items-center gap-1.5 font-bold">
                        <span class="font-mono bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded">{{ $entry->item->sku }}</span>
                        <span>•</span>
                        <span class="flex items-center text-slate-500 bg-slate-50 border border-slate-100 rounded px-1.5 py-0.5">
                            <svg class="w-3 h-3 mr-0.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            {{ $entry->location->name }}
                        </span>
                    </div>
                </div>
                <span class="px-2.5 py-1 text-[10px] rounded-full font-bold tracking-wider uppercase {{ $entry->status === 'pending' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                    {{ ucfirst($entry->status) }}
                </span>
            </div>

            <div class="flex justify-between items-center pt-2.5 border-t border-slate-100">
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
                <div class="flex items-center space-x-3.5">
                    <div class="text-right">
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Petugas</p>
                        <p class="text-xs text-slate-600 font-bold mt-0.5 truncate max-w-[90px]">{{ $entry->petugas->full_name ?? $entry->petugas->name }}</p>
                    </div>
                    <a href="{{ route('verification.show', $entry) }}" class="px-4 py-2 bg-blue-50 border border-blue-100 hover:bg-blue-100 text-blue-700 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center">
                        Detail
                        <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-8 text-center">
            <div class="w-16 h-16 mx-auto bg-slate-50 rounded-2xl flex items-center justify-center mb-3 shadow-inner">
                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <p class="text-slate-500 font-semibold text-sm">Belum ada data hitungan</p>
            <p class="text-slate-400 text-xs mt-1">Data yang diinput oleh petugas SO akan muncul di sini.</p>
        </div>
        @endforelse
    </div>
    @endif

    @push('scripts')
    <script>
        // Real-time client-side filtering
        const filterInput = document.getElementById('filter-input');
        if (filterInput) {
            filterInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const cards = document.querySelectorAll('.entry-card');
                let foundAny = false;
                
                cards.forEach(card => {
                    const sku = card.getAttribute('data-sku').toLowerCase();
                    const name = card.getAttribute('data-name').toLowerCase();
                    const location = card.getAttribute('data-location').toLowerCase();
                    const petugas = card.getAttribute('data-petugas').toLowerCase();
                    
                    if (sku.includes(query) || name.includes(query) || location.includes(query) || petugas.includes(query)) {
                        card.classList.remove('hidden');
                        foundAny = true;
                    } else {
                        card.classList.add('hidden');
                    }
                });
            });
        }
    </script>
    @endpush
</x-layouts.mobile>