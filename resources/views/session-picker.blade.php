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
                <button type="submit" class="w-full text-left group relative bg-white rounded-3xl p-5 border border-slate-100/80 shadow-[0_4px_20px_-2px_rgba(15,23,42,0.05),0_2px_6px_-1px_rgba(15,23,42,0.02)] hover:shadow-[0_10px_25px_-3px_rgba(15,23,42,0.08),0_4px_10px_-2px_rgba(15,23,42,0.04)] active:scale-[0.98] transition-all duration-200 ease-out">
                    <div class="flex items-center gap-4">
                        <div class="flex-1 min-w-0 pr-1">
                            <h2 class="text-base font-bold text-slate-900 leading-snug tracking-tight mb-1 truncate">{{ $item['session']->name }}</h2>
                            <p class="text-sm font-normal text-slate-600 leading-normal mb-1.5 flex flex-wrap items-center gap-x-1.5">
                                <span>Tim Anda:</span>
                                <span class="font-semibold text-blue-600">{{ $item['team']?->name ?? '-' }}</span>
                                @if($item['team']?->leader)
                                    <span class="text-slate-400 font-bold text-xs">•</span>
                                    <span class="text-slate-600">TL: {{ $item['team']->leader->full_name ?? $item['team']->leader->name }}</span>
                                @endif
                            </p>
                            <p class="text-xs text-slate-400 leading-relaxed font-normal line-clamp-2">
                                Dimulai {{ $item['session']->started_at?->format('d M Y H:i') }} • {{ $item['session']->description ?? 'Tanpa deskripsi' }}
                            </p>
                        </div>
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
