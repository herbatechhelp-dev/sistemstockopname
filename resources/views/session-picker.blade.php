<x-layouts.mobile title="Pilih Sesi SO">
    <div class="pt-2">
        <div class="mb-4">
            <h2 class="text-lg font-bold text-slate-900">Pilih Sesi Stock Opname</h2>
            <p class="text-xs text-slate-500 mt-1">Terdapat lebih dari satu sesi SO aktif. Pilih sesi yang sedang Anda kerjakan.</p>
        </div>

        <div class="space-y-3">
            @foreach($items as $item)
            <form method="POST" action="{{ route('session.select') }}">
                @csrf
                <input type="hidden" name="session_id" value="{{ $item['session']->id }}">
                <input type="hidden" name="redirect" value="{{ $redirect }}">
                <button type="submit" class="w-full text-left bg-white rounded-2xl shadow-sm border border-slate-200/60 p-4 hover:border-blue-400 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-11 h-11 rounded-xl bg-blue-600/10 border border-blue-500/20 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ $item['session']->name }}</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    Tim Anda: <span class="font-semibold text-blue-600">{{ $item['team']?->name ?? '-' }}</span>
                                    @if($item['team']?->leader)
                                        • TL: {{ $item['team']->leader->full_name ?? $item['team']->leader->name }}
                                    @endif
                                </p>
                                <p class="text-[10px] text-slate-400 mt-0.5">
                                    Dimulai {{ $item['session']->started_at?->format('d M Y H:i') }}
                                    @if($item['session']->description)
                                        • {{ $item['session']->description }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </button>
            </form>
            @endforeach
        </div>

        <form method="POST" action="{{ route('session.clear') }}" class="mt-6 text-center">
            @csrf
        </form>
    </div>
</x-layouts.mobile>
