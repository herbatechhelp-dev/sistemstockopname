<x-layouts.mobile title="Detail Entri">
    <div class="mb-4 flex items-center justify-between">
        <a href="{{ route('verification.index') }}" class="inline-flex items-center text-slate-500 hover:text-slate-800 text-xs font-bold transition-all">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Daftar
        </a>
        <span class="px-2.5 py-1 text-[10px] rounded-full font-bold tracking-wider uppercase flex-shrink-0 {{ $entry->status === 'pending' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
            {{ $entry->status }}
        </span>
    </div>

    {{-- Details Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 mb-5">
        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1.5">Informasi Item</p>
        <h2 class="font-extrabold text-slate-800 text-base leading-snug mb-2">{{ $entry->item->name }}</h2>
        <span class="font-mono bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-xs font-bold">{{ $entry->item->sku }}</span>

        <div class="grid grid-cols-2 gap-4 mt-5 pt-4 border-t border-slate-100">
            <div>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Lokasi</p>
                <p class="text-sm font-bold text-slate-700 mt-1 flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    {{ $entry->location->name }}
                </p>
            </div>
            <div>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Nomor Batch</p>
                <p class="text-sm font-mono font-bold text-slate-700 mt-1 flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    {{ $entry->batch_code ?? '-' }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-slate-100">
            <div>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Kuantitas Fisik</p>
                <div class="flex items-baseline space-x-1 mt-0.5">
                    <p class="text-3xl font-black text-slate-900">{{ number_format($entry->fisik_qty, 0) }}</p>
                    <p class="text-xs font-bold text-slate-400 uppercase">{{ $entry->uom }}</p>
                </div>
            </div>
            <div>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Petugas SO</p>
                <p class="text-sm font-bold text-slate-700 mt-1.5 flex items-center">
                    <span class="w-5 h-5 rounded-full bg-blue-100 border border-blue-200 text-[10px] font-black text-blue-700 flex items-center justify-center mr-1.5 uppercase">
                        {{ substr($entry->petugas->name, 0, 2) }}
                    </span>
                    {{ $entry->petugas->full_name ?? $entry->petugas->name }}
                </p>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100">
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Keterangan Petugas</p>
            <p class="text-sm font-medium text-slate-700 mt-1 italic leading-relaxed">
                {{ $entry->keterangan ?? 'Tidak ada keterangan.' }}
            </p>
        </div>

        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[9px] text-slate-400 font-bold uppercase tracking-wider">
            <span>Waktu: {{ $entry->created_at->setTimezone('Asia/Jakarta')->format('d M Y H:i') }}</span>
            <span>IP: {{ $entry->ip_address ?? '-' }}</span>
        </div>
    </div>

    {{-- Revision History Timeline --}}
    @if($entry->revisions->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5 mb-5">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Riwayat Perubahan</h3>
        <div class="relative pl-6 border-l-2 border-slate-100 space-y-4">
            @foreach($entry->revisions as $rev)
            <div class="relative">
                <!-- Timeline Dot -->
                <div class="absolute -left-[31px] top-1 w-3 h-3 rounded-full bg-blue-500 border-2 border-white shadow-sm"></div>
                <div>
                    <div class="flex items-center space-x-1.5">
                        <span class="font-black text-slate-700 text-xs">{{ $rev->old_fisik_qty }}</span>
                        <span class="text-slate-400 text-[10px] font-bold">&rarr;</span>
                        <span class="font-black text-blue-600 text-xs">{{ $rev->new_fisik_qty }} {{ $entry->uom }}</span>
                    </div>
                    <p class="text-[10px] text-slate-500 font-medium mt-0.5">{{ $rev->reason }}</p>
                    <p class="text-[9px] text-slate-400/80 font-bold mt-0.5">
                        Oleh: {{ $rev->changer->name ?? 'User' }} • {{ $rev->created_at->setTimezone('Asia/Jakarta')->format('H:i - d M Y') }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Edit Form (only visible if pending) --}}
    @if($entry->status === 'pending')
    <div class="bg-amber-50/50 border border-amber-200/80 rounded-2xl p-5 mb-5 shadow-sm">
        <h3 class="text-sm font-bold text-amber-900 mb-4 flex items-center">
            <svg class="w-4.5 h-4.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Koreksi Data (Team Leader)
        </h3>
        
        <form method="POST" action="{{ route('verification.update', $entry) }}" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">Qty Fisik Terkoreksi</label>
                <div class="flex items-center space-x-3 mb-2">
                    <button type="button" id="qty-minus" class="w-12 h-12 bg-white border border-amber-200 text-slate-700 rounded-xl text-lg font-black hover:bg-amber-100/50 active:scale-95 transition-all flex items-center justify-center shadow-sm">-</button>
                    <input type="number" name="fisik_qty" id="fisik_qty" value="{{ $entry->fisik_qty }}" min="0" step="1" required 
                        class="flex-1 text-center text-2xl font-black py-2 bg-white border border-amber-200 rounded-xl focus:ring-4 focus:ring-amber-100 focus:border-amber-500 transition-all">
                    <button type="button" id="qty-plus" class="w-12 h-12 bg-white border border-amber-200 text-slate-700 rounded-xl text-lg font-black hover:bg-amber-100/50 active:scale-95 transition-all flex items-center justify-center shadow-sm">+</button>
                </div>
                
                {{-- Preset Adjusters --}}
                <div class="grid grid-cols-5 gap-1.5">
                    <button type="button" data-adjust="-5" class="qty-adjust py-1.5 bg-white border border-amber-200 text-amber-800 text-[10px] font-bold rounded-lg active:scale-95 transition-all">-5</button>
                    <button type="button" data-adjust="-1" class="qty-adjust py-1.5 bg-white border border-amber-200 text-amber-800 text-[10px] font-bold rounded-lg active:scale-95 transition-all">-1</button>
                    <button type="button" data-adjust="1" class="qty-adjust py-1.5 bg-white border border-amber-200 text-amber-800 text-[10px] font-bold rounded-lg active:scale-95 transition-all">+1</button>
                    <button type="button" data-adjust="5" class="qty-adjust py-1.5 bg-white border border-amber-200 text-amber-800 text-[10px] font-bold rounded-lg active:scale-95 transition-all">+5</button>
                    <button type="button" id="qty-zero" class="py-1.5 bg-white border border-amber-200 text-amber-800 text-[10px] font-bold rounded-lg active:scale-95 transition-all">Set 0</button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Kode Batch</label>
                <input type="text" name="batch_code" value="{{ $entry->batch_code }}" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm focus:ring-4 focus:ring-blue-100 focus:border-blue-600 transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5" id="keterangan-label">Keterangan Koreksi</label>
                <textarea name="keterangan" id="keterangan" rows="2" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm focus:ring-4 focus:ring-blue-100 focus:border-blue-600 transition-all resize-none">{{ $entry->keterangan }}</textarea>
                @error('keterangan') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-xl shadow-md transition-all active:scale-[0.98]">
                SIMPAN KOREKSI DATA
            </button>
        </form>
    </div>

    {{-- Verify Button --}}
    @if($entry->status === 'pending')
    <form method="POST" action="{{ route('verification.verify', $entry) }}" onsubmit="return confirm('Verifikasi data ini?')">
        @csrf
        <button type="submit" class="w-full py-4 bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-base font-extrabold rounded-2xl shadow-lg shadow-emerald-600/25 hover:shadow-emerald-600/35 active:scale-[0.98] transition-all">
            VERIFIKASI & SELESAI
        </button>
    </form>
    @endif
    @endif

    @push('scripts')
    <script>
        const qtyInput = document.getElementById('fisik_qty');
        if (qtyInput) {
            document.getElementById('qty-minus').addEventListener('click', () => {
                qtyInput.value = Math.max(0, parseFloat(qtyInput.value || 0) - 1);
                checkQtyZero();
            });
            document.getElementById('qty-plus').addEventListener('click', () => {
                qtyInput.value = parseFloat(qtyInput.value || 0) + 1;
                checkQtyZero();
            });
            qtyInput.addEventListener('input', checkQtyZero);

            // Adjustment buttons
            document.querySelectorAll('.qty-adjust').forEach(btn => {
                btn.addEventListener('click', function() {
                    const adjustment = parseFloat(this.getAttribute('data-adjust'));
                    qtyInput.value = Math.max(0, parseFloat(qtyInput.value || 0) + adjustment);
                    checkQtyZero();
                });
            });

            document.getElementById('qty-zero').addEventListener('click', () => {
                qtyInput.value = '0';
                checkQtyZero();
            });

            function checkQtyZero() {
                const qty = parseFloat(qtyInput.value);
                const label = document.getElementById('keterangan-label');
                if (qty === 0) {
                    label.innerHTML = 'Keterangan Koreksi <span class="text-red-500 font-bold">* (Wajib diisi jika Qty 0)</span>';
                } else {
                    label.innerHTML = 'Keterangan Koreksi';
                }
            }

            // Run check initially
            checkQtyZero();
        }
    </script>
    @endpush
</x-layouts.mobile>
