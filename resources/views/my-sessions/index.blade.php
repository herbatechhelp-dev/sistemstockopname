<x-layouts.mobile title="Sesi Saya">
    <div class="mb-4">
        <h2 class="text-lg font-extrabold text-slate-900">Sesi Saya</h2>
        <p class="text-xs text-slate-500 mt-1">Daftar semua Sesi SO yang pernah Anda ikuti — termasuk yang sudah selesai. Tetap blind count, hanya data tim Anda.</p>
    </div>

    {{-- Filter status — scrollable tanpa scrollbar --}}
    <style>.no-scrollbar::-webkit-scrollbar{display:none;}</style>
    <div class="flex gap-2 mb-4 overflow-x-auto pb-1 no-scrollbar" style="scrollbar-width:none; -ms-overflow-style:none;">
        @php $filters = ['' => 'Semua', 'active' => 'Aktif', 'completed' => 'Selesai', 'closed' => 'Ditutup', 'draft' => 'Draft']; @endphp
        @foreach($filters as $val => $label)
            <a href="{{ route('my-sessions.index', $val ? ['status'=>$val] : []) }}"
               class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap border {{ ($statusFilter ?? '') === $val ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if($items->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-8 text-center">
            <div class="w-16 h-16 mx-auto bg-slate-100 rounded-2xl flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <p class="text-slate-600 font-bold text-sm">Belum ada sesi</p>
            <p class="text-slate-400 text-xs mt-1">Anda belum dimasukkan ke tim manapun.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($items as $row)
                @php
                    $s = $row['session'];
                    $team = $row['team'];
                    $badgeClass = match($s->status) {
                        'active' => 'bg-emerald-50 text-emerald-700 border-emerald-300',
                        'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'closed' => 'bg-slate-100 text-slate-600 border-slate-200',
                        default => 'bg-amber-50 text-amber-700 border-amber-200',
                    };
                    $dotClass = match($s->status) {
                        'active' => 'bg-emerald-500 animate-pulse',
                        'completed' => 'bg-blue-500',
                        'closed' => 'bg-slate-400',
                        default => 'bg-amber-500',
                    };
                @endphp
                <a href="{{ route('my-sessions.show', $s) }}" class="block">
                <article class="bg-white rounded-3xl border border-slate-200/90 shadow-[0_4px_20px_-2px_rgba(15,23,42,0.06),0_2px_6px_-1px_rgba(15,23,42,0.04)] overflow-hidden transition-all duration-200 hover:shadow-lg">
                    <div class="p-5">
                        <header class="flex items-start justify-between gap-3 mb-2.5">
                            <h2 class="text-base font-bold text-slate-900 tracking-tight leading-snug line-clamp-1" title="{{ $s->name }}">{{ $s->name }}</h2>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold border shrink-0 {{ $badgeClass }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                {{ strtoupper($s->status) }}
                            </span>
                        </header>
                        <p class="text-[13px] text-slate-500 leading-relaxed mb-3.5 line-clamp-2">{{ $s->description ?? 'Tanpa deskripsi' }}</p>
                        <div class="space-y-1.5 mb-5">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-semibold text-blue-600 bg-blue-50/70 border border-blue-100">{{ $team?->name ?? 'Tanpa tim' }}</span>
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                            </div>
                            @if($team?->leader)
                            <div class="flex items-center gap-1.5 text-xs font-medium text-slate-500 pl-0.5">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                                <span class="text-slate-600">TL: {{ $team->leader->full_name ?? $team->leader->name }}</span>
                            </div>
                            @endif
                        </div>
                        <hr class="border-t border-slate-100 mb-4"/>
                        <div class="grid grid-cols-4 gap-2 bg-slate-50/80 p-3 rounded-2xl border border-slate-100">
                            <div class="flex flex-col items-center justify-center text-center">
                                <span class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">ENTRI TIM</span>
                                <span class="text-lg font-bold text-slate-800 leading-tight mt-1">{{ $row['total_entries_team'] }}</span>
                            </div>
                            <div class="flex flex-col items-center justify-center text-center">
                                <span class="text-[10px] font-bold tracking-wider text-amber-500 uppercase">PENDING</span>
                                <span class="text-lg font-bold text-amber-600 leading-tight mt-1">{{ $row['pending_team'] }}</span>
                            </div>
                            <div class="flex flex-col items-center justify-center text-center">
                                <span class="text-[10px] font-bold tracking-wider text-emerald-600 uppercase">VERIFIED</span>
                                <span class="text-lg font-bold text-emerald-600 leading-tight mt-1">{{ $row['verified_team'] }}</span>
                            </div>
                            <div class="flex flex-col items-center justify-center text-center pl-1 border-l border-slate-200/80">
                                <span class="text-[10px] font-bold tracking-wider text-slate-500 uppercase">ENTRI SAYA</span>
                                <span class="text-lg font-bold text-blue-600 leading-tight mt-1">{{ $row['my_entries'] }}</span>
                                <span class="text-[10px] text-slate-400 font-medium">{{ $row['allocations_count'] }} lokasi</span>
                            </div>
                        </div>
                    </div>
                    <footer class="bg-slate-50/50 px-5 py-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-1.5 text-[11px] text-slate-400 font-medium tracking-tight">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                            <span>Dibuat {{ $s->created_at->format('d M Y') }} @if($s->started_at) • Mulai {{ $s->started_at->format('d M H:i') }} @endif</span>
                        </div>
                        <span class="inline-flex items-center gap-1 font-semibold text-blue-600 hover:text-blue-700 transition-colors py-1 pl-2 text-[13px] group">
                            <span>Detail</span>
                            <svg class="w-3.5 h-3.5 transform transition-transform group-hover:translate-x-0.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path></svg>
                        </span>
                    </footer>
                </article>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.mobile>
