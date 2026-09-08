<x-layouts.mobile title="Detail Sesi" sessionName="{{ $session->name }}">
    <div class="mb-4">
        <a href="{{ route('my-sessions.index') }}" class="inline-flex items-center text-slate-500 hover:text-slate-800 text-xs font-bold">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Sesi Saya
        </a>
    </div>

    {{-- Session header — Modern Detail Card (font & gap disesuaikan, lebih modern) --}}
    <div class="bg-white rounded-[28px] border border-slate-200/70 shadow-[0_12px_32px_-8px_rgba(15,23,42,0.08),0_4px_12px_-2px_rgba(15,23,42,0.04)] overflow-hidden mb-5">
        {{-- Top accent --}}
        <div class="h-1 w-full bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600"></div>
        <div class="p-6 sm:p-7">
        <header class="space-y-4 pb-6 border-b border-slate-100">
            <div class="flex items-start justify-between gap-4">
                <div class="flex gap-3.5">
                    <div class="hidden sm:flex w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 items-center justify-center shadow-md shadow-blue-600/20 shrink-0">
                        <svg class="w-5.5 h-5.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div class="space-y-1.5">
                        <h1 class="text-[22px] sm:text-[23px] font-[800] tracking-[-0.02em] text-slate-900 leading-[1.25]">{{ $session->name }}</h1>
                        <p class="text-[13px] sm:text-[14px] leading-[1.6] text-slate-500 font-[450] line-clamp-2">{{ $session->description ?? 'Tanpa deskripsi' }}</p>
                    </div>
                </div>
                @php $badge = match($session->status) { 'active'=>'bg-emerald-50 text-emerald-700 border-emerald-200', 'completed'=>'bg-blue-50 text-blue-700 border-blue-200', 'closed'=>'bg-white text-slate-600 border-slate-200', default=>'bg-amber-50 text-amber-700 border-amber-200' }; @endphp
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-[0.08em] border shadow-sm {{ $badge }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $session->status==='active' ? 'bg-emerald-500 animate-pulse' : ($session->status==='completed' ? 'bg-blue-500' : 'bg-slate-400') }}"></span>
                        {{ strtoupper($session->status) }}
                    </span>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-[12.5px] font-medium">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200 text-slate-600">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Dibuat {{ $session->created_at->format('d M Y • H:i') }}
                </span>
                @if($session->started_at)<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700">Mulai {{ $session->started_at->format('d M • H:i') }}</span>@endif
                @if($session->ended_at)<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-600">Selesai {{ $session->ended_at->format('d M • H:i') }}</span>@endif
            </div>
        </header>

        @if($team)
        <section class="pt-6 space-y-4">
            <div class="space-y-2.5">
                <p class="text-[10px] font-extrabold tracking-[0.14em] text-slate-400 uppercase">
                    <span class="w-6 h-[2px] bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full"></span>
                    TIM ANDA
                </p>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[16px] font-[800] tracking-tight text-slate-900 leading-none">{{ $team->name }}</span>
                    <span class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-slate-600 bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-full leading-none">
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                        TL: {{ $team->leader->full_name ?? $team->leader->name }}
                    </span>
                </div>
            </div>
            <div class="flex flex-wrap gap-1.5" data-purpose="team-members">
                @foreach($team->members as $m)
                    <span class="inline-flex items-center gap-1.5 pl-1 pr-2.5 py-1 rounded-full text-[12.5px] font-semibold text-slate-700 bg-white border border-slate-200 shadow-sm hover:border-slate-300 transition-colors leading-none">
                        <span class="w-6 h-6 rounded-full bg-gradient-to-br from-slate-800 to-slate-700 text-white flex items-center justify-center text-[10px] font-bold shrink-0">{{ strtoupper(substr($m->user->name,0,2)) }}</span>
                        {{ $m->user->full_name ?? $m->user->name }}
                    </span>
                @endforeach
            </div>
            <div class="space-y-3 pt-1" data-purpose="location-racks">
                <p class="text-[10px] font-extrabold tracking-[0.12em] text-slate-400 uppercase">LOKASI DIALOKASIKAN</p>
                @forelse($team->locationAllocations as $alloc)
                    <div class="flex items-center gap-3 px-4 py-3.5 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50/60 border border-blue-200/70 shadow-sm">
                        <span class="w-8 h-8 rounded-xl bg-white border border-blue-200 flex items-center justify-center shadow-sm shrink-0">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </span>
                        <span class="text-[13.5px] font-bold tracking-tight text-blue-800 select-all">{{ $alloc->location->name }}</span>
                    </div>
                @empty
                    <span class="text-xs text-slate-400">Belum ada alokasi lokasi</span>
                @endforelse
            </div>
        </section>
        @else
            <p class="mt-5 text-xs font-bold text-amber-600 bg-amber-50 border border-amber-200 rounded-xl p-3">Anda tidak terdaftar di tim manapun pada sesi ini (riwayat saja).</p>
        @endif

        @if($session->status !== 'active')
            <div class="mt-6 p-3.5 bg-amber-50/70 border border-amber-200 rounded-2xl flex items-start gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-white border border-amber-200 flex items-center justify-center shadow-sm shrink-0">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <p class="text-[13px] font-semibold text-amber-800 leading-relaxed">Mode riwayat — input dinonaktifkan (SO sudah <span class="font-extrabold">{{ $session->status }}</span>).</p>
            </div>
        @endif
        </div>
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
