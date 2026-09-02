<x-layouts.mobile title="Sesi Saya">
    <div class="mb-4">
        <h2 class="text-lg font-extrabold text-slate-900">Sesi Saya</h2>
        <p class="text-xs text-slate-500 mt-1">Daftar semua Sesi SO yang pernah Anda ikuti — termasuk yang sudah selesai. Tetap blind count, hanya data tim Anda.</p>
    </div>

    {{-- Filter status --}}
    <div class="flex gap-2 mb-4 overflow-x-auto pb-1">
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
                    $badge = match($s->status) {
                        'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'closed' => 'bg-slate-100 text-slate-600 border-slate-200',
                        default => 'bg-amber-50 text-amber-700 border-amber-200',
                    };
                @endphp
                <a href="{{ route('my-sessions.show', $s) }}" class="block bg-white rounded-2xl shadow-sm border border-slate-200/60 p-4 hover:shadow-md hover:border-blue-200 transition-all">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1 min-w-0 pr-2">
                            <p class="text-sm font-extrabold text-slate-900 truncate">{{ $s->name }}</p>
                            <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1">{{ $s->description ?? 'Tanpa deskripsi' }}</p>
                            <div class="text-[11px] text-slate-500 mt-1 flex flex-wrap items-center gap-1.5">
                                <span class="font-semibold text-blue-600">{{ $team?->name ?? 'Tanpa tim' }}</span>
                                @if($team?->leader)
                                    <span class="text-slate-300">•</span>
                                    <span>TL: {{ $team->leader->full_name ?? $team->leader->name }}</span>
                                @endif
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border {{ $badge }}">{{ $s->status }}</span>
                    </div>
                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                        <div class="flex gap-4">
                            <div class="text-center">
                                <p class="text-[10px] font-bold text-slate-400 uppercase">Entri Tim</p>
                                <p class="text-sm font-black text-slate-800">{{ $row['total_entries_team'] }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-[10px] font-bold text-amber-600 uppercase">Pending</p>
                                <p class="text-sm font-black text-amber-700">{{ $row['pending_team'] }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-[10px] font-bold text-emerald-600 uppercase">Verified</p>
                                <p class="text-sm font-black text-emerald-700">{{ $row['verified_team'] }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] text-slate-400 font-bold uppercase">Entri Saya</p>
                            <p class="text-sm font-black text-blue-700">{{ $row['my_entries'] }}</p>
                            <p class="text-[10px] text-slate-400">{{ $row['allocations_count'] }} lokasi</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-[10px] text-slate-400">Dibuat {{ $s->created_at->format('d M Y') }} @if($s->started_at) • Mulai {{ $s->started_at->format('d M H:i') }} @endif</span>
                        <span class="text-xs font-bold text-blue-600 flex items-center">Detail <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg></span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.mobile>
