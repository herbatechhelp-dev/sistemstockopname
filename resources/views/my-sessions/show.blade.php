<x-layouts.mobile title="Detail Sesi" sessionName="{{ $session->name }}">
    <div class="mb-4">
        <a href="{{ route('my-sessions.index') }}" class="inline-flex items-center text-slate-500 hover:text-slate-800 text-xs font-bold">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Sesi Saya
        </a>
    </div>

    {{-- Session header --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 mb-4">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">{{ $session->name }}</h2>
                <p class="text-xs text-slate-500 mt-1">{{ $session->description ?? 'Tanpa deskripsi' }}</p>
                <p class="text-[11px] text-slate-400 mt-2">Dibuat {{ $session->created_at->format('d M Y H:i') }} @if($session->started_at) • Mulai {{ $session->started_at->format('d M Y H:i') }} @endif @if($session->ended_at) • Selesai {{ $session->ended_at->format('d M Y H:i') }} @endif</p>
            </div>
            @php $badge = match($session->status) { 'active'=>'bg-emerald-50 text-emerald-700 border-emerald-200', 'completed'=>'bg-blue-50 text-blue-700 border-blue-200', 'closed'=>'bg-slate-100 text-slate-600 border-slate-200', default=>'bg-amber-50 text-amber-700 border-amber-200' }; @endphp
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase border {{ $badge }}">{{ $session->status }}</span>
        </div>

        @if($team)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tim Anda</p>
                <p class="text-sm font-extrabold text-slate-800 mt-1">{{ $team->name }} <span class="text-xs font-semibold text-slate-500">• TL: {{ $team->leader->full_name ?? $team->leader->name }}</span></p>
                <div class="flex flex-wrap gap-1.5 mt-2">
                    @foreach($team->members as $m)
                        <span class="px-2 py-1 bg-slate-50 border border-slate-200 rounded-full text-[11px] font-semibold text-slate-700">{{ $m->user->full_name ?? $m->user->name }}</span>
                    @endforeach
                </div>
                <div class="flex flex-wrap gap-1.5 mt-3">
                    @forelse($team->locationAllocations as $alloc)
                        <span class="px-2 py-1 bg-blue-50 border border-blue-100 rounded-lg text-[11px] font-semibold text-blue-700">{{ $alloc->location->name }}</span>
                    @empty
                        <span class="text-xs text-slate-400">Belum ada alokasi lokasi</span>
                    @endforelse
                </div>
            </div>
        @else
            <p class="mt-4 text-xs font-bold text-amber-600 bg-amber-50 border border-amber-200 rounded-xl p-3">Anda tidak terdaftar di tim manapun pada sesi ini (riwayat saja).</p>
        @endif

        @if($session->status !== 'active')
            <div class="mt-4 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                <p class="text-xs font-bold text-slate-600 flex items-center"><svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Mode riwayat — input dinonaktifkan (SO sudah {{ $session->status }}).</p>
            </div>
        @endif
    </div>

    {{-- Entries read-only --}}
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Entri Tim ({{ $entries->count() }})</h3>
        <span class="text-[10px] text-slate-400">Blind count — tanpa stok sistem</span>
    </div>

    @if($entries->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/60 p-8 text-center">
            <p class="text-sm font-semibold text-slate-500">Belum ada entri di sesi ini</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($entries as $entry)
                <div class="bg-white rounded-2xl border-l-4 {{ $entry->status === 'verified' ? 'border-emerald-500' : ($entry->status === 'pending' ? 'border-amber-400' : 'border-blue-400') }} border-y border-r border-slate-200/60 p-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1 min-w-0 pr-2">
                            <p class="text-sm font-extrabold text-slate-800 truncate">{{ $entry->item->name }}</p>
                            <p class="text-[11px] text-slate-400 mt-1"><span class="font-mono bg-slate-100 px-1.5 py-0.5 rounded font-bold">{{ $entry->item->sku }}</span> • {{ $entry->location->name }}</p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase {{ $entry->status==='verified' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">{{ $entry->status }}</span>
                    </div>
                    <div class="flex justify-between items-end mt-3 pt-3 border-t border-slate-100">
                        <div>
                            <p class="text-xl font-black text-slate-900">{{ number_format($entry->fisik_qty,0) }} <span class="text-xs font-bold text-slate-400">{{ $entry->uom }}</span></p>
                            @if($entry->batch_code)<p class="text-[11px] text-slate-500">Batch: <span class="font-mono">{{ $entry->batch_code }}</span></p>@endif
                            @if($entry->keterangan)<p class="text-[11px] text-slate-500 mt-1">“{{ $entry->keterangan }}”</p>@endif
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Petugas</p>
                            <p class="text-xs font-bold text-slate-700">{{ $entry->petugas->full_name ?? $entry->petugas->name }}</p>
                            <p class="text-[10px] text-slate-400">{{ $entry->created_at->format('d M H:i') }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.mobile>
